<?php
require_once __DIR__ . '/auth/auth_check.php';
require_auth();

$fullname = $_SESSION['fullname'] ?? 'NHRE User';
$email = $_SESSION['email'] ?? '';
$role = $_SESSION['role'] ?? 'User';

$medicines = [
    ['name' => 'Paracetamol 500mg', 'category' => 'Pain Relief', 'stock' => 'In stock', 'price' => '৳ 4', 'description' => 'Common medicine for fever, headache, and mild pain relief.'],
    ['name' => 'Naproxen 250mg', 'category' => 'Pain Relief', 'stock' => 'In stock', 'price' => '৳ 18', 'description' => 'Useful for joint pain, muscle pain, and inflammatory discomfort.'],
    ['name' => 'Ibuprofen 400mg', 'category' => 'Pain Relief', 'stock' => 'Limited', 'price' => '৳ 22', 'description' => 'Popular anti-inflammatory medicine for pain and swelling.'],
    ['name' => 'Amoxicillin 250mg', 'category' => 'Antibiotic', 'stock' => 'In stock', 'price' => '৳ 35', 'description' => 'Widely used antibiotic for bacterial infections.'],
    ['name' => 'Azithromycin 500mg', 'category' => 'Antibiotic', 'stock' => 'In stock', 'price' => '৳ 55', 'description' => 'Commonly prescribed for respiratory and throat infections.'],
    ['name' => 'Cefixime 200mg', 'category' => 'Antibiotic', 'stock' => 'Limited', 'price' => '৳ 48', 'description' => 'Broad-spectrum antibiotic often used for common bacterial ailments.'],
    ['name' => 'Metronidazole 400mg', 'category' => 'Antibiotic', 'stock' => 'In stock', 'price' => '৳ 26', 'description' => 'Used for certain infections and parasitic conditions.'],
    ['name' => 'Ciprofloxacin 500mg', 'category' => 'Antibiotic', 'stock' => 'Limited', 'price' => '৳ 40', 'description' => 'Prescription antibiotic for several bacterial infections.'],
    ['name' => 'Omeprazole 20mg', 'category' => 'Gastrointestinal', 'stock' => 'In stock', 'price' => '৳ 18', 'description' => 'Acid reducer used for acidity, reflux, and ulcers.'],
    ['name' => 'Ranitidine 150mg', 'category' => 'Gastrointestinal', 'stock' => 'Limited', 'price' => '৳ 16', 'description' => 'Used for acidity and stomach ulcer related symptoms.'],
    ['name' => 'Domperidone 10mg', 'category' => 'Gastrointestinal', 'stock' => 'In stock', 'price' => '৳ 20', 'description' => 'Helps relieve nausea and improve stomach movement.'],
    ['name' => 'Hydroxyzine 25mg', 'category' => 'Allergy', 'stock' => 'In stock', 'price' => '৳ 28', 'description' => 'Used for allergy symptoms, itching, and anxiety relief.'],
    ['name' => 'Cetirizine 10mg', 'category' => 'Allergy', 'stock' => 'In stock', 'price' => '৳ 15', 'description' => 'Common antihistamine for sneezing, runny nose, and rashes.'],
    ['name' => 'Loratadine 10mg', 'category' => 'Allergy', 'stock' => 'In stock', 'price' => '৳ 17', 'description' => 'Used for seasonal allergies and mild allergic reactions.'],
    ['name' => 'Vitamin C 1000mg', 'category' => 'Supplements', 'stock' => 'In stock', 'price' => '৳ 24', 'description' => 'Supports immunity and daily wellness needs.'],
    ['name' => 'Vitamin D3 1000 IU', 'category' => 'Supplements', 'stock' => 'In stock', 'price' => '৳ 30', 'description' => 'Helps support bone strength and vitamin D levels.'],
    ['name' => 'Calcium + Vitamin D', 'category' => 'Supplements', 'stock' => 'In stock', 'price' => '৳ 35', 'description' => 'Common supplement for bone health and daily nutrition.'],
    ['name' => 'Omega 3 Capsule', 'category' => 'Supplements', 'stock' => 'Limited', 'price' => '৳ 42', 'description' => 'Supports heart and brain health.'],
    ['name' => 'Multivitamin Tablet', 'category' => 'Supplements', 'stock' => 'In stock', 'price' => '৳ 28', 'description' => 'Daily supplement for essential vitamins and minerals.'],
    ['name' => 'Insulin Glargine', 'category' => 'Diabetes Care', 'stock' => 'Limited', 'price' => '৳ 1800', 'description' => 'Long-acting insulin commonly used in diabetes management.'],
    ['name' => 'Metformin 500mg', 'category' => 'Diabetes Care', 'stock' => 'In stock', 'price' => '৳ 22', 'description' => 'Common oral medicine for type 2 diabetes control.'],
    ['name' => 'Gliclazide 80mg', 'category' => 'Diabetes Care', 'stock' => 'In stock', 'price' => '৳ 30', 'description' => 'Used to help control blood sugar in diabetic patients.'],
    ['name' => 'Amlodipine 5mg', 'category' => 'Cardiology', 'stock' => 'In stock', 'price' => '৳ 18', 'description' => 'Blood pressure medication used for hypertension control.'],
    ['name' => 'Atorvastatin 20mg', 'category' => 'Cardiology', 'stock' => 'In stock', 'price' => '৳ 35', 'description' => 'Used to lower cholesterol and lower heart risk.'],
    ['name' => 'Losartan 50mg', 'category' => 'Cardiology', 'stock' => 'Limited', 'price' => '৳ 28', 'description' => 'Common antihypertensive medicine for blood pressure.'],
    ['name' => 'Aspirin 75mg', 'category' => 'Cardiology', 'stock' => 'In stock', 'price' => '৳ 12', 'description' => 'Used for heart protection and mild pain relief.'],
    ['name' => 'Salbutamol Inhaler', 'category' => 'Respiratory', 'stock' => 'In stock', 'price' => '৳ 180', 'description' => 'Relief inhaler used for wheezing and asthma symptoms.'],
    ['name' => 'Beclomethasone Inhaler', 'category' => 'Respiratory', 'stock' => 'Limited', 'price' => '৳ 220', 'description' => 'Controller inhaler used for long-term asthma management.'],
    ['name' => 'Montelukast 10mg', 'category' => 'Respiratory', 'stock' => 'In stock', 'price' => '৳ 40', 'description' => 'Helps control asthma and allergy-related breathing issues.'],
    ['name' => 'Amoxicillin-Clavulanic Acid', 'category' => 'Antibiotic', 'stock' => 'In stock', 'price' => '৳ 60', 'description' => 'Combination antibiotic for broader bacterial coverage.'],
    ['name' => 'Doxycycline 100mg', 'category' => 'Antibiotic', 'stock' => 'Limited', 'price' => '৳ 32', 'description' => 'Common antibiotic used for many bacterial infections.'],
    ['name' => 'Levofloxacin 500mg', 'category' => 'Antibiotic', 'stock' => 'Limited', 'price' => '৳ 44', 'description' => 'Broad-spectrum antibiotic used for respiratory and urinary issues.'],
    ['name' => 'Albendazole 400mg', 'category' => 'Anti-parasitic', 'stock' => 'In stock', 'price' => '৳ 18', 'description' => 'Used for deworming and parasitic infections.'],
    ['name' => 'Mebendazole 100mg', 'category' => 'Anti-parasitic', 'stock' => 'In stock', 'price' => '৳ 16', 'description' => 'Common anti-worm medicine for intestinal parasites.'],
    ['name' => 'Fluconazole 150mg', 'category' => 'Antifungal', 'stock' => 'In stock', 'price' => '৳ 28', 'description' => 'Used for fungal infections like candidiasis.'],
    ['name' => 'Clotrimazole Cream', 'category' => 'Antifungal', 'stock' => 'In stock', 'price' => '৳ 24', 'description' => 'Topical antifungal for skin infections.'],
    ['name' => 'Ketoconazole Shampoo', 'category' => 'Antifungal', 'stock' => 'Limited', 'price' => '৳ 35', 'description' => 'Used for dandruff and fungal scalp conditions.'],
    ['name' => 'Cough Syrup', 'category' => 'Respiratory', 'stock' => 'In stock', 'price' => '৳ 26', 'description' => 'Supportive cough medicine for common cold symptoms.'],
    ['name' => 'Guaifenesin 200mg', 'category' => 'Respiratory', 'stock' => 'In stock', 'price' => '৳ 24', 'description' => 'Expectorant used to loosen mucus and ease cough.'],
    ['name' => 'Pseudoephidrine', 'category' => 'Respiratory', 'stock' => 'Limited', 'price' => '৳ 30', 'description' => 'Used for nasal congestion and cold symptoms.'],
    ['name' => 'Lorazepam 2mg', 'category' => 'Neurology', 'stock' => 'Limited', 'price' => '৳ 40', 'description' => 'Used for anxiety and short-term sleep support under guidance.'],
    ['name' => 'Escitalopram 10mg', 'category' => 'Neurology', 'stock' => 'Limited', 'price' => '৳ 55', 'description' => 'Prescription medicine for anxiety and depression.'],
    ['name' => 'Paroxetine 20mg', 'category' => 'Neurology', 'stock' => 'Limited', 'price' => '৳ 48', 'description' => 'Common antidepressant used in mental health treatment.'],
    ['name' => 'Diazepam 5mg', 'category' => 'Neurology', 'stock' => 'Limited', 'price' => '৳ 35', 'description' => 'Used for anxiety, muscle spasm, and seizure-related conditions.'],
    ['name' => 'Lansoprazole 30mg', 'category' => 'Gastrointestinal', 'stock' => 'In stock', 'price' => '৳ 25', 'description' => 'Acid suppression medicine used for reflux and ulcers.'],
    ['name' => 'Pantoprazole 40mg', 'category' => 'Gastrointestinal', 'stock' => 'In stock', 'price' => '৳ 28', 'description' => 'Proton pump inhibitor for acidity and related digestive issues.'],
    ['name' => 'Sodium Bicarbonate', 'category' => 'Gastrointestinal', 'stock' => 'In stock', 'price' => '৳ 12', 'description' => 'Used as an antacid for temporary relief of heartburn.'],
    ['name' => 'Oral Rehydration Salts', 'category' => 'Emergency Care', 'stock' => 'In stock', 'price' => '৳ 14', 'description' => 'Essential hydration solution for dehydration caused by diarrhea.'],
    ['name' => 'Glucose Powder', 'category' => 'Emergency Care', 'stock' => 'In stock', 'price' => '৳ 16', 'description' => 'Energy supplement used for quick recovery and hydration support.'],
    ['name' => 'B Complex Tablet', 'category' => 'Supplements', 'stock' => 'In stock', 'price' => '৳ 20', 'description' => 'Helps support metabolism and energy production.'],
    ['name' => 'Iron Tablet', 'category' => 'Supplements', 'stock' => 'In stock', 'price' => '৳ 18', 'description' => 'Common supplement for iron deficiency and anemia support.'],
    ['name' => 'Folic Acid 5mg', 'category' => 'Supplements', 'stock' => 'In stock', 'price' => '৳ 10', 'description' => 'Supports red blood cell formation and pregnancy nutrition.'],
    ['name' => 'Naproxen 500mg', 'category' => 'Pain Relief', 'stock' => 'Limited', 'price' => '৳ 30', 'description' => 'Higher-dose pain medicine for stronger relief.'],
    ['name' => 'Levocetirizine 5mg', 'category' => 'Allergy', 'stock' => 'In stock', 'price' => '৳ 20', 'description' => 'Second-generation antihistamine for allergy symptoms.'],
    ['name' => 'Budesonide Nasal Spray', 'category' => 'Allergy', 'stock' => 'Limited', 'price' => '৳ 95', 'description' => 'Used for allergic rhinitis and nasal congestion.'],
    ['name' => 'Hydrochlorothiazide 25mg', 'category' => 'Cardiology', 'stock' => 'Limited', 'price' => '৳ 16', 'description' => 'Diuretic used for fluid retention and high blood pressure.'],
    ['name' => 'Glimepiride 2mg', 'category' => 'Diabetes Care', 'stock' => 'In stock', 'price' => '৳ 26', 'description' => 'Oral medicine used to control blood sugar levels.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pharmacy - NHRE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styles.css?v=20260807-2">
<script>
  (function () {
    try {
      var t = localStorage.getItem("nhre-theme");
      if (t !== "light" && t !== "dark") {
        t = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
      }
      document.documentElement.dataset.theme = t;
      document.documentElement.style.colorScheme = t;
    } catch (e) {}
  })();
</script>
</head>
<body class="dashboard-body">
  <nav class="dashboard-nav">
    <div class="container d-flex align-items-center justify-content-between gap-3">
      <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
        <img src="assets/images/nhre-logo.svg" alt="NHRE" class="nhre-logo-img">
      </a>
      <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn btn-dashboard-logout ripple">
          <i class="fa-solid fa-house"></i>
          <span>Dashboard</span>
        </a>
        <a href="logout.php" class="btn btn-dashboard-logout ripple">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
          <span>Logout</span>
        </a>
      </div>
    </div>
  </nav>

  <main class="dashboard-main">
    <section class="container">
      <div class="dashboard-hero glass-card">
        <div>
          <span class="auth-kicker">Pharmacy Section</span>
          <h1>Medicine access for <?= e($fullname) ?></h1>
          <p>Browse essential medicines, check availability, and request prescriptions quickly.</p>
        </div>
        <div class="dashboard-user-pill">
          <i class="fa-solid fa-pills"></i>
          <span><?= e($role) ?></span>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="row g-4">
            <?php foreach ($medicines as $medicine): ?>
              <div class="col-md-6">
                <article class="dashboard-card">
                  <div class="dashboard-card-icon"><i class="fa-solid fa-capsules"></i></div>
                  <h2><?= e($medicine['name']) ?></h2>
                  <p class="mb-2"><strong><?= e($medicine['category']) ?></strong></p>
                  <p><?= e($medicine['description']) ?></p>
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="badge bg-light text-dark"><?= e($medicine['stock']) ?></span>
                    <span class="fw-bold text-teal"><?= e($medicine['price']) ?></span>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-lg-4">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-file-medical"></i></div>
            <h2>Request Pharmacy Support</h2>
            <p>Need urgent medication assistance? Submit a request and our team will follow up.</p>
            <form class="mt-3">
              <div class="mb-3">
                <label class="form-label">Medicine Name</label>
                <input type="text" class="form-control" placeholder="e.g. Paracetamol">
              </div>
              <div class="mb-3">
                <label class="form-label">Prescription Notes</label>
                <textarea class="form-control" rows="4" placeholder="Add dosage or urgency details"></textarea>
              </div>
              <button type="submit" class="btn btn-solid-nhre w-100">
                <i class="fa-solid fa-paper-plane"></i> Submit Request
              </button>
            </form>
          </article>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js?v=20260807-2"></script>
</body>
</html>
