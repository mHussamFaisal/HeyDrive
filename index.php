<?php
require_once 'includes/config.php';
require_once 'payments/payment_config.php';

// Set up DB connection for language/maps settings
$pdo = db_connect();

// Language support from settings
$lang_setting = 'en';
try {
    $stmt_lang = $pdo->query("SELECT setting_value FROM td_settings WHERE setting_key = 'default_language' LIMIT 1");
    if ($stmt_lang) { $lang_val = $stmt_lang->fetchColumn(); if ($lang_val) $lang_setting = $lang_val; }
} catch(Exception $e){}

$translations = [
    'en' => [
        'book_your_taxi'=>'Book Your Taxi','fill_details'=>'Fill in the details below to reserve your ride',
        'personal_info'=>'Personal Information','full_name'=>'Full Name','phone_number'=>'Phone Number',
        'email_address'=>'Email Address','trip_details'=>'Trip Details','pickup_address'=>'Pickup Address',
        'dropoff_address'=>'Drop-off Address','pickup_date'=>'Pickup Date','pickup_time'=>'Pickup Time',
        'passengers'=>'Passengers','vehicle_payment'=>'Vehicle & Payment','vehicle_type'=>'Vehicle Type',
        'flight_number'=>'Flight Number (optional)','special_notes'=>'Special Notes','book_now'=>'Book Now',
        'enter_pickup'=>'Enter pickup location','enter_destination'=>'Enter destination',
        'passenger'=>'Passenger','book_title'=>'Book a Taxi','track_booking'=>'Track Booking',
        'services'=>'Services','contact'=>'Contact',
    ],
    'de' => [
        'book_your_taxi'=>'Ihr Taxi buchen','fill_details'=>'Füllen Sie die Felder aus, um Ihre Fahrt zu reservieren',
        'personal_info'=>'Persönliche Informationen','full_name'=>'Vollständiger Name','phone_number'=>'Telefonnummer',
        'email_address'=>'E-Mail-Adresse','trip_details'=>'Fahrtdetails','pickup_address'=>'Abholadresse',
        'dropoff_address'=>'Zieladresse','pickup_date'=>'Abholdatum','pickup_time'=>'Abholzeit',
        'passengers'=>'Passagiere','vehicle_payment'=>'Fahrzeug & Zahlung','vehicle_type'=>'Fahrzeugtyp',
        'flight_number'=>'Flugnummer (optional)','special_notes'=>'Besondere Wünsche','book_now'=>'Jetzt buchen',
        'enter_pickup'=>'Abholort eingeben','enter_destination'=>'Reiseziel eingeben',
        'passenger'=>'Passagier','book_title'=>'Taxi buchen','track_booking'=>'Buchung verfolgen',
        'services'=>'Leistungen','contact'=>'Kontakt',
    ],
    'fr' => [
        'book_your_taxi'=>'Réservez votre taxi','fill_details'=>'Remplissez les détails ci-dessous pour réserver votre course',
        'personal_info'=>'Informations personnelles','full_name'=>'Nom complet','phone_number'=>'Numéro de téléphone',
        'email_address'=>'Adresse e-mail','trip_details'=>'Détails du trajet','pickup_address'=>'Adresse de prise en charge',
        'dropoff_address'=>'Adresse de destination','pickup_date'=>'Date de prise en charge','pickup_time'=>'Heure de prise en charge',
        'passengers'=>'Passagers','vehicle_payment'=>'Véhicule & Paiement','vehicle_type'=>'Type de véhicule',
        'flight_number'=>'Numéro de vol (optionnel)','special_notes'=>'Notes spéciales','book_now'=>'Réserver maintenant',
        'enter_pickup'=>'Entrez le lieu de prise en charge','enter_destination'=>'Entrez la destination',
        'passenger'=>'Passager','book_title'=>'Réserver un taxi','track_booking'=>'Suivre la réservation',
        'services'=>'Services','contact'=>'Contact',
    ],
    'nl' => [
        'book_your_taxi'=>'Taxi boeken','fill_details'=>'Vul de onderstaande gegevens in om uw rit te reserveren',
        'personal_info'=>'Persoonlijke informatie','full_name'=>'Volledige naam','phone_number'=>'Telefoonnummer',
        'email_address'=>'E-mailadres','trip_details'=>'Ritgegevens','pickup_address'=>'Ophaaladres',
        'dropoff_address'=>'Bestemmingsadres','pickup_date'=>'Ophaaldatum','pickup_time'=>'Ophaaltijd',
        'passengers'=>'Passagiers','vehicle_payment'=>'Voertuig & Betaling','vehicle_type'=>'Voertuigtype',
        'flight_number'=>'Vluchtnummer (optioneel)','special_notes'=>'Speciale verzoeken','book_now'=>'Nu boeken',
        'enter_pickup'=>'Voer ophaallocatie in','enter_destination'=>'Voer bestemming in',
        'passenger'=>'Passagier','book_title'=>'Taxi boeken','track_booking'=>'Boeking volgen',
        'services'=>'Diensten','contact'=>'Contact',
    ],
    'es' => [
        'book_your_taxi'=>'Reservar su taxi','fill_details'=>'Complete los detalles a continuación para reservar su viaje',
        'personal_info'=>'Información personal','full_name'=>'Nombre completo','phone_number'=>'Número de teléfono',
        'email_address'=>'Dirección de correo electrónico','trip_details'=>'Detalles del viaje','pickup_address'=>'Dirección de recogida',
        'dropoff_address'=>'Dirección de destino','pickup_date'=>'Fecha de recogida','pickup_time'=>'Hora de recogida',
        'passengers'=>'Pasajeros','vehicle_payment'=>'Vehículo y Pago','vehicle_type'=>'Tipo de vehículo',
        'flight_number'=>'Número de vuelo (opcional)','special_notes'=>'Notas especiales','book_now'=>'Reservar ahora',
        'enter_pickup'=>'Ingrese el lugar de recogida','enter_destination'=>'Ingrese el destino',
        'passenger'=>'Pasajero','book_title'=>'Reservar un taxi','track_booking'=>'Seguir reserva',
        'services'=>'Servicios','contact'=>'Contacto',
    ],
];
$t = $translations[$lang_setting] ?? $translations['en'];
$lang_dir = in_array($lang_setting, ['ar', 'he']) ? 'rtl' : 'ltr';

// Google Maps API Key from settings
$gmaps_key = '';
try {
    $stmt_gm = $pdo->query("SELECT setting_value FROM td_settings WHERE setting_key = 'google_maps_js_key' LIMIT 1");
    if ($stmt_gm) { $gk = $stmt_gm->fetchColumn(); if ($gk && $gk !== 'YOUR_GOOGLE_MAPS_API_KEY') $gmaps_key = $gk; }
} catch(Exception $e){}


// Handle booking form submission
$success = false;
$error = '';
$booking_ref = '';
$show_payment = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_taxi'])) {
    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $pickup  = sanitize($_POST['pickup_address'] ?? '');
    $dropoff = sanitize($_POST['dropoff_address'] ?? '');
    $pdate   = sanitize($_POST['pickup_date'] ?? '');
    $ptime   = sanitize($_POST['pickup_time'] ?? '');
    $passengers = intval($_POST['passengers'] ?? 1);
    $vtype   = sanitize($_POST['vehicle_type'] ?? 'sedan');
    $flight  = sanitize($_POST['flight_number'] ?? '');
    $notes   = sanitize($_POST['notes'] ?? '');

    if ($name && $phone && $pickup && $dropoff && $pdate && $ptime) {
        $pickup_datetime = $pdate . ' ' . $ptime . ':00';
        $ref = 'TD' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        // Estimate fare from pricing
        $fare = 0;
        try {
            $pr = $pdo->prepare("SELECT * FROM td_pricing WHERE vehicle_type=? LIMIT 1");
            $pr->execute([$vtype]);
            $pricing = $pr->fetch(PDO::FETCH_ASSOC);
            if($pricing) $fare = (float)$pricing['min_fare'];
        } catch(Exception $e){}

        try {
            $stmt = $pdo->prepare("INSERT INTO td_bookings 
                (booking_ref, customer_name, customer_email, customer_phone, 
                 pickup_address, dropoff_address, pickup_datetime, passengers, 
                 vehicle_type, flight_number, notes, payment_method, fare, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'pending')");
            $stmt->execute([$ref, $name, $email, $phone, $pickup, $dropoff, 
                           $pickup_datetime, $passengers, $vtype, $flight, $notes, $fare > 0 ? $fare : null]);
            $booking_ref = $ref;
            $success = true;
            $show_payment = true;
        } catch (Exception $e) {
            $error = "Booking failed. Please try again or call us directly.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Load active payment gateways for display
$active_gateways = [];
try { $active_gateways = get_active_gateways(); } catch(Exception $e){}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang_setting); ?>" dir="<?php echo $lang_dir; ?>">
<head><meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TaxisDispatch - Book Your Taxi</title>
<link rel="icon" href="assets/favicon.ico" type="image/x-icon">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
<?php if ($gmaps_key): ?>
<style>
/* Google Maps Autocomplete dropdown styling */
.pac-container {
    z-index: 99999 !important;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    font-family: inherit;
    margin-top: 2px;
}
.pac-item {
    padding: 8px 14px;
    cursor: pointer;
    font-size: 14px;
    border-top: 1px solid #f0f0f0;
}
.pac-item:hover, .pac-item-selected {
    background: #fff8e1;
}
.pac-icon { margin-top: 8px; }
.pac-item-query { font-weight: 600; color: #333; }
.pac-matched { color: #f5a623; }
/* Remove spinner and fix input-group layout */
.address-autocomplete-wrap { position: relative; }
</style>
<script>
function initGoogleMaps() {
    var pickupInput  = document.getElementById('pickup_address');
    var dropoffInput = document.getElementById('dropoff_address');
    if (!pickupInput || !dropoffInput) return;

    var opts = {
        types: ['geocode', 'establishment'],
        fields: ['formatted_address', 'geometry', 'name']
    };

    var pickupAC  = new google.maps.places.Autocomplete(pickupInput,  opts);
    var dropoffAC = new google.maps.places.Autocomplete(dropoffInput, opts);

    // When user selects a suggestion, fill in formatted address
    pickupAC.addListener('place_changed', function() {
        var place = pickupAC.getPlace();
        if (place.formatted_address) {
            pickupInput.value = place.formatted_address;
        }
    });
    dropoffAC.addListener('place_changed', function() {
        var place = dropoffAC.getPlace();
        if (place.formatted_address) {
            dropoffInput.value = place.formatted_address;
        }
    });

    // Prevent form submission when pressing Enter inside autocomplete fields
    [pickupInput, dropoffInput].forEach(function(inp) {
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var selected = document.querySelector('.pac-item-selected');
                if (selected || document.querySelector('.pac-container:not([style*="display: none"])')) {
                    e.preventDefault();
                }
            }
        });
    });
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($gmaps_key); ?>&libraries=places&callback=initGoogleMaps" async defer></script>
<?php endif; ?>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">
      <i class="fas fa-taxi me-2 text-warning"></i>TaxisDispatch
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#book"><?php echo $t['book_title']; ?></a></li>
        <li class="nav-item"><a class="nav-link" href="#services"><?php echo $t['services']; ?></a></li>
        <li class="nav-item"><a class="nav-link" href="#contact"><?php echo $t['contact']; ?></a></li>
        <li class="nav-item"><a class="nav-link" href="track.php"><i class="fas fa-map-marker-alt"></i> <?php echo $t['track_booking']; ?></a></li>
        <li class="nav-item"><a class="nav-link btn btn-warning text-dark px-3 ms-2" href="admin/">Admin</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="hero-section text-white py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-4">
        <h1 class="display-4 fw-bold">Fast & Reliable<br><span class="text-warning">Taxi Service</span></h1>
        <p class="lead">Professional taxi and dispatch service. Book online, track in real-time.</p>
        <div class="d-flex gap-3 mt-3">
          <div><i class="fas fa-check-circle text-warning me-1"></i> 24/7 Available</div>
          <div><i class="fas fa-check-circle text-warning me-1"></i> Fixed Prices</div>
          <div><i class="fas fa-check-circle text-warning me-1"></i> Professional Drivers</div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-stats d-flex gap-3 justify-content-lg-end">
          <div class="stat-card text-center p-3 rounded bg-white text-dark">
            <div class="h3 text-warning fw-bold mb-0">500+</div>
            <small>Happy Customers</small>
          </div>
          <div class="stat-card text-center p-3 rounded bg-white text-dark">
            <div class="h3 text-warning fw-bold mb-0">50+</div>
            <small>Professional Drivers</small>
          </div>
          <div class="stat-card text-center p-3 rounded bg-white text-dark">
            <div class="h3 text-warning fw-bold mb-0">24/7</div>
            <small>Service Hours</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Booking Form -->
<section id="book" class="py-5 bg-light">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="fw-bold"><?php echo $t['book_your_taxi']; ?></h2>
      <p class="text-muted"><?php echo $t['fill_details']; ?></p>
    </div>

    <?php if ($success && $show_payment): ?>
    <!-- Step 2: Payment Selection -->
    <div class="card shadow border-0 rounded-4 overflow-hidden">
      <div class="card-header bg-dark text-white py-3">
        <div class="d-flex align-items-center gap-3">
          <i class="fas fa-check-circle fa-2x text-success"></i>
          <div>
            <h5 class="mb-0 fw-bold">Booking Received!</h5>
            <small class="text-muted">Reference: <span class="text-warning fw-bold"><?= $booking_ref ?></span></small>
          </div>
        </div>
      </div>
      <div class="card-body p-4">
        <h5 class="fw-bold mb-1"><i class="fas fa-credit-card me-2 text-warning"></i>Choose Your Payment Method</h5>
        <p class="text-muted small mb-4">Select how you'd like to pay for your journey. Your booking is secured regardless of payment timing.</p>
        <div class="row g-3">
          <?php foreach($active_gateways as $gw): ?>
          <?php
            $icons = ['stripe'=>'fab fa-stripe-s','paypal'=>'fab fa-paypal','square'=>'fas fa-square','sumup'=>'fas fa-credit-card','cash'=>'fas fa-money-bill-wave','invoice'=>'fas fa-file-invoice'];
            $colors = ['stripe'=>'#635bff','paypal'=>'#003087','square'=>'#00b140','sumup'=>'#131415','cash'=>'#198754','invoice'=>'#0d6efd'];
            $descs = ['stripe'=>'Pay securely by credit or debit card','paypal'=>'Pay via your PayPal account or card','square'=>'Pay securely via Square checkout','sumup'=>'Pay securely via SumUp checkout','cash'=>'Pay the driver directly in cash','invoice'=>'Receive an invoice for bank transfer'];
            $icon = $icons[$gw['gateway']] ?? 'fas fa-credit-card';
            $color = $colors[$gw['gateway']] ?? '#6c757d';
            $desc = $descs[$gw['gateway']] ?? '';
          ?>
          <div class="col-md-4 col-6">
            <a href="/payments/process.php?ref=<?= urlencode($booking_ref) ?>&gateway=<?= urlencode($gw['gateway']) ?>"
               class="text-decoration-none">
              <div class="card h-100 border-2 payment-option-card" style="cursor:pointer;transition:all .2s"
                   onmouseover="this.style.borderColor='<?= $color ?>'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,.12)'"
                   onmouseout="this.style.borderColor=''; this.style.transform=''; this.style.boxShadow=''">
                <div class="card-body text-center p-3">
                  <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                       style="width:52px;height:52px;background:<?= $color ?>20">
                    <i class="<?= $icon ?> fa-lg" style="color:<?= $color ?>"></i>
                  </div>
                  <div class="fw-bold small"><?= htmlspecialchars($gw['label']) ?></div>
                  <div class="text-muted" style="font-size:.72rem"><?= $desc ?></div>
                  <?php if($gw['test_mode'] && !in_array($gw['gateway'],['cash','invoice'])): ?>
                  <span class="badge bg-warning text-dark mt-1" style="font-size:.65rem">Test Mode</span>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
          <?php if(empty($active_gateways)): ?>
          <div class="col-12">
            <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Payment methods are being configured. A team member will contact you to arrange payment.</div>
          </div>
          <?php endif; ?>
        </div>
        <hr class="my-4">
        <div class="d-flex gap-2 flex-wrap">
          <a href="track.php?ref=<?= $booking_ref ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-search me-1"></i>Track Booking
          </a>
          <a href="/" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-plus me-1"></i>New Booking
          </a>
        </div>
      </div>
    </div>
    <?php elseif ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= $error ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <form method="POST" action="#book">
          <div class="row g-3">
            <!-- Personal Info -->
            <div class="col-12"><h5 class="border-bottom pb-2"><i class="fas fa-user me-2 text-warning"></i><?php echo $t['personal_info']; ?></h5></div>
            <div class="col-md-4">
              <label class="form-label"><?php echo $t['full_name']; ?> <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" placeholder="John Smith" required>
            </div>
            <div class="col-md-4">
              <label class="form-label"><?php echo $t['phone_number']; ?> <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" name="phone" placeholder="+49 123 456789" required>
            </div>
            <div class="col-md-4">
              <label class="form-label"><?php echo $t['email_address']; ?></label>
              <input type="email" class="form-control" name="email" placeholder="john@example.com">
            </div>

            <!-- Trip Details -->
            <div class="col-12 mt-2"><h5 class="border-bottom pb-2"><i class="fas fa-map-marked-alt me-2 text-warning"></i><?php echo $t['trip_details']; ?></h5></div>
            <div class="col-md-6">
              <label class="form-label"><?php echo $t['pickup_address']; ?> <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text text-success"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" id="pickup_address" class="form-control" name="pickup_address" autocomplete="off" placeholder="<?php echo $t['enter_pickup']; ?>" value="<?php echo htmlspecialchars($_GET['pickup_address'] ?? ''); ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label"><?php echo $t['dropoff_address']; ?> <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text text-danger"><i class="fas fa-flag-checkered"></i></span>
                <input type="text" id="dropoff_address" class="form-control" name="dropoff_address" autocomplete="off" placeholder="<?php echo $t['enter_destination']; ?>" value="<?php echo htmlspecialchars($_GET['dropoff_address'] ?? ''); ?>" required>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label"><?php echo $t['pickup_date']; ?> <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="pickup_date" 
                     min="<?= date('Y-m-d') ?>" value="<?php echo htmlspecialchars($_GET['pickup_date'] ?? date('Y-m-d')); ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label"><?php echo $t['pickup_time']; ?> <span class="text-danger">*</span></label>
              <input type="time" class="form-control" name="pickup_time" value="<?php echo htmlspecialchars($_GET['pickup_time'] ?? date('H:i')); ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label"><?php echo $t['passengers']; ?></label>
              <select class="form-select" name="passengers">
                <?php for ($i=1;$i<=8;$i++): ?>
                <option value="<?=$i?>"><?=$i?> Passenger<?= $i>1?'s':'' ?></option>
                <?php endfor; ?>
              </select>
            </div>

            <!-- Vehicle & Payment -->
            <div class="col-12 mt-2"><h5 class="border-bottom pb-2"><i class="fas fa-car me-2 text-warning"></i><?php echo $t['vehicle_payment']; ?></h5></div>
            <div class="col-md-4">
              <label class="form-label"><?php echo $t['vehicle_type']; ?></label>
              <select class="form-select" name="vehicle_type">
                <option value="sedan">🚗 Sedan (up to 4 pax)</option>
                <option value="van">🚐 Van/MPV (up to 7 pax)</option>
                <option value="luxury">🚙 Luxury / Business</option>
                <option value="minibus">🚌 Minibus (up to 16 pax)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="fas fa-credit-card me-1 text-warning"></i>Payment</label>
              <div class="form-control bg-light text-muted" style="cursor:default">
                <i class="fas fa-lock me-1 text-success"></i> Choose after booking
                <?php if(!empty($active_gateways)): ?>
                <div class="mt-1">
                  <?php foreach(array_slice($active_gateways,0,4) as $gw): ?>
                  <?php $icons2=['stripe'=>'fab fa-stripe-s','paypal'=>'fab fa-paypal','square'=>'fas fa-square','sumup'=>'fas fa-credit-card','cash'=>'fas fa-money-bill-wave','invoice'=>'fas fa-file-invoice']; ?>
                  <i class="<?=$icons2[$gw['gateway']]??'fas fa-credit-card'?> me-1 text-secondary" title="<?=htmlspecialchars($gw['label'])?>"></i>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label"><?php echo $t['flight_number']; ?></label>
              <input type="text" class="form-control" name="flight_number" placeholder="e.g. LH1234">
            </div>
            <div class="col-12">
              <label class="form-label"><?php echo $t['special_notes']; ?></label>
              <textarea class="form-control" name="notes" rows="2" placeholder="Any special requirements, wheelchair access, child seat, etc."></textarea>
            </div>
            <div class="col-12 text-center mt-3">
              <button type="submit" name="book_taxi" class="btn btn-warning btn-lg px-5 fw-bold shadow">
                <i class="fas fa-taxi me-2"></i><?php echo $t['book_now']; ?>
              </button>
              <p class="text-muted small mt-2"><i class="fas fa-lock me-1"></i>Secure booking • Free cancellation up to 2 hours before pickup</p>
            </div>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Services Section -->
<section id="services" class="py-5">
  <div class="container">
    <h2 class="text-center fw-bold mb-4">Our Services</h2>
    <div class="row g-4">
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm h-100 p-3">
          <div class="display-4 mb-2">✈️</div>
          <h5>Airport Transfers</h5>
          <p class="text-muted small">Reliable airport pickups and drop-offs. Flight tracking included.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm h-100 p-3">
          <div class="display-4 mb-2">🏢</div>
          <h5>Business Travel</h5>
          <p class="text-muted small">Professional service for corporate clients. Invoice billing available.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm h-100 p-3">
          <div class="display-4 mb-2">🎉</div>
          <h5>Events & Tours</h5>
          <p class="text-muted small">Wedding, events, and sightseeing tours with experienced drivers.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm h-100 p-3">
          <div class="display-4 mb-2">🚐</div>
          <h5>Group Transport</h5>
          <p class="text-muted small">Minibus and van services for groups up to 16 passengers.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5 bg-dark text-white">
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-4">
        <h4><i class="fas fa-taxi text-warning me-2"></i>TaxisDispatch</h4>
        <p class="text-muted">Professional taxi and dispatch services available 24/7.</p>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="text-warning">Contact Us</h6>
        <p><i class="fas fa-phone me-2"></i>+49 123 456789</p>
        <p><i class="fas fa-envelope me-2"></i>info@taxisdispatch.com</p>
        <p><i class="fas fa-clock me-2"></i>24/7 Service</p>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="text-warning">Quick Links</h6>
        <ul class="list-unstyled">
          <li><a href="#book" class="text-muted text-decoration-none">📋 Book a Taxi</a></li>
          <li><a href="track.php" class="text-muted text-decoration-none">📍 Track Booking</a></li>
          <li><a href="admin/" class="text-muted text-decoration-none">🔐 Admin Panel</a></li>
          <li><a href="driver/" class="text-muted text-decoration-none">🚖 Driver Portal</a></li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary">
    <p class="text-center text-muted small mb-0">&copy; <?= date('Y') ?> TaxisDispatch. All rights reserved.</p>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
