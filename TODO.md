# Mobile-first Filament Upgrade TODO

## Step 1: Resource tables -> mobile list/cards (no desktop-table shrink)
- [x] Update UserResource.php: mobile columns + ••• dropdown actions
- [x] Update DemographicResource.php
- [ ] Update AssessmentResource.php
- [x] Update PredictionResource.php
- [x] Update RecommendationResource.php


## Step 2: Forms responsiveness
- [ ] Update UserResource form columns (mobile 1 col)
- [ ] Update AssessmentResource sections (desktop 3, tablet 2, mobile 1)
- [ ] Update RecommendationResource / any other resource forms

## Step 3: Dashboard + widgets (mobile-first spacing/typography)
- [ ] Update overview-charts.blade.php
- [ ] Review widget classes for columnSpan/gaps if needed

## Step 4: Sidebar + drawer (hamburger, full screen, auto-close)
- [ ] Update AdminPanelProvider.php

## Step 5: CSS audit + global tighten
- [ ] Audit px-8/10/12, max-w-6xl/7xl, whitespace-nowrap, table-auto/fixed, overflow, w-screen
- [ ] Fix/override with mobile-first CSS (correct filament-custom css path)

## Step 6: Verification
- [ ] Confirm: no desktop layout on <1024px
- [ ] Confirm: no horizontal scroll
- [ ] Confirm: no desktop table on HP

