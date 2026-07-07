"""
BurnoutShield — Model Retraining Script
Run: python retrain.py [path/to/dataset.csv]
"""
import os, sys, json, time, warnings
import numpy as np
import pandas as pd
import joblib
from sklearn.ensemble import RandomForestClassifier, GradientBoostingClassifier
from sklearn.neighbors import KNeighborsClassifier
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import LabelEncoder, StandardScaler
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, roc_auc_score

warnings.filterwarnings("ignore")

DATASET = sys.argv[1] if len(sys.argv) > 1 else "data/tech_mental_health_burnout.csv"
MODEL_DIR = os.path.join(os.path.dirname(__file__), "models")
os.makedirs(MODEL_DIR, exist_ok=True)

print(f"Loading dataset: {DATASET}")
df = pd.read_csv(DATASET)
df.drop_duplicates(inplace=True)

# Outlier removal
for col in ['age','work_hours_per_week','overtime_hours','sleep_hours','screen_time_hours']:
    if col in df.columns:
        Q1, Q3 = df[col].quantile(0.01), df[col].quantile(0.99)
        df = df[(df[col] >= Q1) & (df[col] <= Q3)]

# Encode categoricals
cat_cols = ['gender','job_role','company_size','work_mode']
encoders = {}
for col in cat_cols:
    if col in df.columns:
        le = LabelEncoder()
        df[col] = le.fit_transform(df[col].astype(str))
        encoders[col] = le

le_target = LabelEncoder()
df['burnout_enc'] = le_target.fit_transform(df['burnout_level'])

FEATURES = ['age','gender','job_role','experience_years','company_size','work_mode',
            'work_hours_per_week','overtime_hours','meetings_per_day','deadlines_missed',
            'job_satisfaction','manager_support','work_life_balance','sleep_hours',
            'physical_activity_days','screen_time_hours','caffeine_intake',
            'social_support_score','has_therapy','stress_level','anxiety_score',
            'depression_score','seeks_professional_help']

X = df[FEATURES].copy()
y = df['burnout_enc'].copy()

# Random oversampling
def random_oversample(X, y):
    X_res, y_res = X.copy(), y.copy()
    max_count = y.value_counts().max()
    np.random.seed(42)
    for cls in y.unique():
        cls_idx = y[y == cls].index
        if len(cls_idx) < max_count:
            n = max_count - len(cls_idx)
            sampled = np.random.choice(cls_idx, size=n, replace=True)
            X_res = pd.concat([X_res, X.loc[sampled]])
            y_res = pd.concat([y_res, y.loc[sampled]])
    return X_res.reset_index(drop=True), y_res.reset_index(drop=True)

print("Balancing dataset...")
X_bal, y_bal = random_oversample(X, y)
print(f"Balanced distribution: {dict(y_bal.value_counts())}")

X_train, X_test, y_train, y_test = train_test_split(X_bal, y_bal, test_size=0.2, random_state=42, stratify=y_bal)

scaler = StandardScaler()
X_tr_sc = scaler.fit_transform(X_train)
X_te_sc = scaler.transform(X_test)

MODELS = {
    'Random Forest':   RandomForestClassifier(n_estimators=100, random_state=42, n_jobs=-1, class_weight='balanced'),
    'KNN':             KNeighborsClassifier(n_neighbors=7),
    'Gradient Boost':  GradientBoostingClassifier(n_estimators=100, random_state=42),
    'Logistic Reg':    LogisticRegression(max_iter=500, random_state=42, class_weight='balanced'),
}

results = {}
print("\nTraining models...")
for name, model in MODELS.items():
    t0 = time.time()
    use_scaled = name in ['KNN', 'Logistic Reg']
    Xtr = X_tr_sc if use_scaled else X_train
    Xte = X_te_sc if use_scaled else X_test

    model.fit(Xtr, y_train)
    y_pred = model.predict(Xte)
    y_prob = model.predict_proba(Xte)

    results[name] = {
        'accuracy':  round(accuracy_score(y_test, y_pred), 4),
        'precision': round(precision_score(y_test, y_pred, average='weighted', zero_division=0), 4),
        'recall':    round(recall_score(y_test, y_pred, average='weighted', zero_division=0), 4),
        'f1':        round(f1_score(y_test, y_pred, average='weighted', zero_division=0), 4),
        'roc_auc':   round(roc_auc_score(y_test, y_prob, multi_class='ovr', average='weighted'), 4),
        'time':      round(time.time()-t0, 2),
    }
    print(f"  {name}: F1={results[name]['f1']:.4f} AUC={results[name]['roc_auc']:.4f}")

best_name = max(results, key=lambda k: results[k]['f1'])
print(f"\n✅ Best model: {best_name} (F1={results[best_name]['f1']})")

best_model = MODELS[best_name]
joblib.dump(best_model, os.path.join(MODEL_DIR, "best_model.pkl"))
joblib.dump(scaler,     os.path.join(MODEL_DIR, "scaler.pkl"))
joblib.dump(le_target,  os.path.join(MODEL_DIR, "label_encoder.pkl"))
joblib.dump(encoders,   os.path.join(MODEL_DIR, "cat_encoders.pkl"))

metrics_data = {
    'best_model': best_name,
    'features': FEATURES,
    'classes': list(le_target.classes_),
    'balancing': 'Random Oversample',
    'models': results,
    'best_metrics': results[best_name],
}
with open(os.path.join(MODEL_DIR, "metrics.json"), 'w') as f:
    json.dump(metrics_data, f, indent=2)

print("✅ Retraining complete. Models saved to models/")
