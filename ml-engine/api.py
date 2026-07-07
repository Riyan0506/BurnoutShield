"""
BurnoutShield ML Engine — FastAPI Server
Run: python api.py
Port: 8001
"""
import json
import os
import sys
import warnings
from typing import Optional

warnings.filterwarnings("ignore")

try:
    from http.server import HTTPServer, BaseHTTPRequestHandler
    import urllib.parse
    import joblib
    import numpy as np
    import pandas as pd
    from sklearn.ensemble import RandomForestClassifier
    from sklearn.preprocessing import LabelEncoder, StandardScaler
except ImportError as e:
    print(f"Missing package: {e}")
    sys.exit(1)

# ─── Load Models ───────────────────────────────────────────
MODEL_DIR = os.path.join(os.path.dirname(__file__), "models")

print("Loading ML models...")
best_model     = joblib.load(os.path.join(MODEL_DIR, "best_model.pkl"))
scaler         = joblib.load(os.path.join(MODEL_DIR, "scaler.pkl"))
label_encoder  = joblib.load(os.path.join(MODEL_DIR, "label_encoder.pkl"))
cat_encoders   = joblib.load(os.path.join(MODEL_DIR, "cat_encoders.pkl"))

with open(os.path.join(MODEL_DIR, "metrics.json")) as f:
    metrics_data = json.load(f)

FEATURES = metrics_data["features"]
CLASSES  = metrics_data["classes"]  # ['High', 'Low', 'Moderate']
print(f"✅ Model loaded: {metrics_data['best_model']}")
print(f"   Classes: {CLASSES}")
print(f"   Features: {len(FEATURES)}")


# ─── Prediction Logic ──────────────────────────────────────
def predict_burnout(data: dict) -> dict:
    """Main prediction function called from Laravel REST client"""
    
    # Map from Laravel field names to dataset field names
    field_map = {
        'age': 'age',
        'gender': 'gender',
        'job_role': 'job_role',
        'experience_years': 'experience_years',
        'company_size': 'company_size',
        'work_mode': 'work_mode',
        'work_hours_per_week': 'work_hours_per_week',
        'overtime_hours': 'overtime_hours',
        'meetings_per_day': 'meetings_per_day',
        'deadlines_missed': 'deadlines_missed',
        'job_satisfaction': 'job_satisfaction',
        'manager_support': 'manager_support',
        'work_life_balance': 'work_life_balance',
        'sleep_hours': 'sleep_hours',
        'physical_activity_days': 'physical_activity_days',
        'screen_time_hours': 'screen_time_hours',
        'caffeine_intake': 'caffeine_intake',
        'social_support_score': 'social_support_score',
        'has_therapy': 'has_therapy',
        'stress_level': 'stress_level',
        'anxiety_score': 'anxiety_score',
        'depression_score': 'depression_score',
        'seeks_professional_help': 'seeks_professional_help',
    }
    
    row = {}
    for feat in FEATURES:
        val = data.get(feat, data.get(field_map.get(feat, feat), 0))
        row[feat] = val
    
    # Encode categorical columns
    cat_cols = ['gender', 'job_role', 'company_size', 'work_mode']
    for col in cat_cols:
        if col in row and col in cat_encoders:
            le = cat_encoders[col]
            val_str = str(row[col])
            if val_str in le.classes_:
                row[col] = int(le.transform([val_str])[0])
            else:
                row[col] = 0
    
    # Build feature vector
    X = np.array([[float(row.get(f, 0)) for f in FEATURES]])
    
    # Predict
    proba      = best_model.predict_proba(X)[0]
    pred_class = best_model.predict(X)[0]
    risk_label = label_encoder.inverse_transform([pred_class])[0]
    
    # Build probability dict: {class_name: prob}
    prob_dict = {}
    for i, cls in enumerate(label_encoder.classes_):
        prob_dict[cls] = round(float(proba[i]) * 100, 2)
    
    # Feature importance (from RandomForest)
    importance = {}
    if hasattr(best_model, 'feature_importances_'):
        imp = best_model.feature_importances_
        for feat, val in zip(FEATURES, imp):
            importance[feat] = round(float(val), 4)
        # Sort descending
        importance = dict(sorted(importance.items(), key=lambda x: x[1], reverse=True))
    
    burnout_prob = prob_dict.get('High', 0) + prob_dict.get('Moderate', 0) * 0.5
    
    return {
        "risk_level": risk_label,
        "burnout_probability": round(burnout_prob, 2),
        "probabilities": prob_dict,
        "model_used": metrics_data["best_model"],
        "feature_importance": importance,
        "top_risk_factors": list(importance.keys())[:5] if importance else [],
    }


# ─── Simple HTTP Server (no external deps needed) ──────────
class MLHandler(BaseHTTPRequestHandler):
    
    def log_message(self, format, *args):
        print(f"[ML API] {self.address_string()} - {format % args}")
    
    def send_json(self, code: int, data: dict):
        body = json.dumps(data).encode()
        self.send_response(code)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", len(body))
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type, Authorization, X-ML-API-Key")
        self.end_headers()
        self.wfile.write(body)
    
    def do_OPTIONS(self):
        self.send_response(200)
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type, Authorization, X-ML-API-Key")
        self.end_headers()
    
    def do_GET(self):
        path = self.path.split("?")[0]
        
        if path == "/health":
            self.send_json(200, {
                "status": "ok",
                "model": metrics_data["best_model"],
                "classes": CLASSES,
                "features": len(FEATURES)
            })
        
        elif path == "/model/info":
            self.send_json(200, {
                "best_model": metrics_data["best_model"],
                "balancing_method": metrics_data.get("balancing", "Random Oversample"),
                "features": FEATURES,
                "classes": CLASSES,
            })
        
        elif path == "/model/performance":
            self.send_json(200, {
                "models": metrics_data.get("models", {}),
                "best": metrics_data.get("best_metrics", {}),
                "best_model": metrics_data["best_model"],
            })
        
        else:
            self.send_json(404, {"error": "Not found"})
    
    def do_POST(self):
        path = self.path.split("?")[0]
        
        # Read body
        length = int(self.headers.get("Content-Length", 0))
        body   = self.rfile.read(length).decode("utf-8")
        
        try:
            payload = json.loads(body) if body else {}
        except json.JSONDecodeError:
            self.send_json(400, {"error": "Invalid JSON"})
            return
        
        if path == "/predict":
            try:
                result = predict_burnout(payload)
                self.send_json(200, result)
            except Exception as e:
                self.send_json(500, {"error": str(e)})
        
        elif path == "/retrain":
            # Placeholder — in production this triggers async retraining
            self.send_json(200, {
                "status": "queued",
                "message": "Retraining job queued (not implemented in dev mode)"
            })
        
        else:
            self.send_json(404, {"error": "Not found"})


if __name__ == "__main__":
    HOST = "0.0.0.0"
    PORT = int(os.environ.get("ML_PORT", 8001))
    
    server = HTTPServer((HOST, PORT), MLHandler)
    print(f"\n🚀 BurnoutShield ML API running on http://{HOST}:{PORT}")
    print(f"   GET  /health              — Health check")
    print(f"   GET  /model/info          — Model info")
    print(f"   GET  /model/performance   — All model metrics")
    print(f"   POST /predict             — Predict burnout risk")
    print(f"   POST /retrain             — Trigger retraining")
    print("\nPress Ctrl+C to stop\n")
    
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\n⛔ Server stopped")
