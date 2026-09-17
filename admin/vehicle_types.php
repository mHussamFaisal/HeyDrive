<?php
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();

// ── GET: Available Gallery Images ──────────────────────────────────────────────
$gallery_images = [];
$gallery_dir = dirname(__DIR__) . '/assets/images/vehicles-types';
if (is_dir($gallery_dir)) {
    $files = scandir($gallery_dir);
    foreach ($files as $f) {
        if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['png','jpg','jpeg','webp','gif'])) {
            $gallery_images[] = $f;
        }
    }
}

// ── POST: Add / Edit / Delete Vehicle Type ─────────────────────────────────────
$msg = $_GET['msg'] ?? '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id            = intval($_POST['id'] ?? 0);
        $name          = trim($_POST['name'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $passengers    = intval($_POST['passengers'] ?? 0);
        $luggage       = intval($_POST['luggage'] ?? 0);
        $hand_luggage  = intval($_POST['hand_luggage'] ?? 0);
        $wheelchair    = intval($_POST['wheelchair'] ?? 0);
        $baby_seats    = intval($_POST['baby_seats'] ?? 0); // Booster seats
        $child_seats   = intval($_POST['child_seats'] ?? 0);
        $infant_seats  = intval($_POST['infant_seats'] ?? 0);
        $ordering      = intval($_POST['ordering'] ?? 0);
        $driver        = trim($_POST['driver'] ?? 'Unassigned');
        $booking_option= trim($_POST['booking_option'] ?? 'Normal booking');
        $is_default    = isset($_POST['is_default']) ? 1 : 0;
        $published     = isset($_POST['published']) ? 1 : 0;
        $is_backend    = intval($_POST['is_backend'] ?? 0); // 0=Frontend & Backend, 1=Backend, 2=Frontend
        $hourly_rate   = floatval($_POST['hourly_rate'] ?? 0);

        // Handle Image selection
        $image_name = trim($_POST['current_image'] ?? '');
        $image_type = intval($_POST['image_type'] ?? 0); // 0=Gallery, 1=Upload

        if ($image_type === 0 && !empty($_POST['image_gallery'])) {
            $image_name = trim($_POST['image_gallery']);
        } elseif ($image_type === 1 && !empty($_FILES['image_upload']['name'])) {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $ftype   = mime_content_type($_FILES['image_upload']['tmp_name']);
            if (in_array($ftype, $allowed)) {
                $ext        = pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION);
                $filename   = 'vehicle_type_' . time() . '_' . rand(100,999) . '.' . strtolower($ext);
                $upload_dir = dirname(__DIR__) . '/uploads/vehicles-types/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $upload_dir . $filename)) {
                    $image_name = $filename;
                }
            }
        }

        if ($is_default) {
            // Unset other defaults
            $pdo->exec("UPDATE eto_vehicle SET `default`=0");
        }

        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE eto_vehicle SET 
                name=?, description=?, image=?, passengers=?, luggage=?, hand_luggage=?, 
                wheelchair=?, baby_seats=?, child_seats=?, infant_seats=?, ordering=?, 
                disable_info=?, `default`=?, published=?, is_backend=?, hourly_rate=?
                WHERE id=?");
            $stmt->execute([
                $name, $description, $image_name, $passengers, $luggage, $hand_luggage,
                $wheelchair, $baby_seats, $child_seats, $infant_seats, $ordering,
                $booking_option, $is_default, $published, $is_backend, $hourly_rate, $id
            ]);
            header("Location: " . APP_URL . "/admin/vehicle_types.php?msg=updated");
            exit;
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO eto_vehicle 
                (name, description, image, passengers, luggage, hand_luggage, 
                 wheelchair, baby_seats, child_seats, infant_seats, ordering, 
                 disable_info, `default`, published, is_backend, hourly_rate, profile_id, user_id)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,0)");
            $stmt->execute([
                $name, $description, $image_name, $passengers, $luggage, $hand_luggage,
                $wheelchair, $baby_seats, $child_seats, $infant_seats, $ordering,
                $booking_option, $is_default, $published, $is_backend, $hourly_rate
            ]);
            header("Location: " . APP_URL . "/admin/vehicle_types.php?msg=created");
            exit;
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM eto_vehicle WHERE id=?")->execute([$id]);
            header("Location: " . APP_URL . "/admin/vehicle_types.php?msg=deleted");
            exit;
        }
    }
}

// ── GET: Fetch all Vehicle Types ───────────────────────────────────────────────
$vehicle_types = [];
try {
    $vehicle_types = $pdo->query("SELECT * FROM eto_vehicle ORDER BY ordering ASC, id ASC")->fetchAll();
} catch (Exception $e) {
    $err = "Database query error: " . htmlspecialchars($e->getMessage());
}

$page_title = 'Types of Vehicles';
require_once 'header.php';
?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item active">Types of Vehicles</li>
  </ol>
</nav>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
  <?= $msg==='created' ? '✅ Vehicle type added successfully!' : ($msg==='updated' ? '✅ Vehicle type updated successfully!' : '🗑️ Vehicle type deleted.') ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Header Toolbar -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-car me-2 text-warning"></i>Types of Vehicles</h4>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-success btn-lg px-3 shadow-sm d-flex align-items-center gap-2 fw-semibold" onclick="openVehicleModal()" style="border-radius:.6rem;">
      <i class="fas fa-plus"></i> Add new
    </button>
  </div>
</div>

<!-- Table Card with Horizontal Scroll -->
<div class="d-flex justify-content-between align-items-center mb-2 px-1">
  <small class="text-muted"><i class="fas fa-arrows-left-right me-1 text-warning"></i> <strong>Horizontal Scroll enabled:</strong> Scroll sideways to view all fleet columns</small>
</div>

<div class="card border-0 shadow-sm rounded-3">
  <div class="card-body p-0">
    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch; max-width: 100%;">
      <table class="table table-hover align-middle mb-0" style="min-width: 1450px; white-space: nowrap;">
        <thead class="table-light small text-muted text-uppercase">
          <tr>
            <th class="ps-3" style="width: 90px; min-width: 90px;">Actions</th>
            <th style="width: 130px; min-width: 130px;">Image</th>
            <th style="min-width: 140px;">Name</th>
            <th style="min-width: 100px;">Driver</th>
            <th style="min-width: 100px;">Services</th>
            <th style="min-width: 120px;">Hourly rate</th>
            <th style="min-width: 250px;">Capacity</th>
            <th style="min-width: 140px;">Booking option</th>
            <th style="min-width: 100px;">Default</th>
            <th style="min-width: 90px;">Active</th>
            <th style="min-width: 100px;">Ordering</th>
            <th class="pe-3" style="min-width: 160px;">Display</th>
          </tr>
        </thead>
        <tbody class="small">
          <?php if (empty($vehicle_types)): ?>
          <tr>
            <td colspan="12" class="text-center py-4 text-muted">No vehicle types found. Click <strong>+ Add new</strong> to create one.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($vehicle_types as $vt): ?>
          <tr>
            <!-- Actions -->
            <td class="ps-3">
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary p-1 px-2" title="Edit" onclick='editVehicleModal(<?= json_encode($vt, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                  <i class="fas fa-pencil-alt"></i>
                </button>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this vehicle type?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $vt['id'] ?>">
                  <button type="submit" class="btn btn-outline-secondary p-1 px-2" title="Delete">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </form>
              </div>
            </td>

            <!-- Image -->
            <td>
              <?php 
              $img_src = '';
              if (!empty($vt['image'])) {
                  if (file_exists(dirname(__DIR__) . '/uploads/vehicles-types/' . $vt['image'])) {
                      $img_src = APP_URL . '/uploads/vehicles-types/' . $vt['image'];
                  } elseif (file_exists(dirname(__DIR__) . '/assets/images/vehicles-types/' . $vt['image'])) {
                      $img_src = APP_URL . '/assets/images/vehicles-types/' . $vt['image'];
                  }
              }
              ?>
              <?php if ($img_src): ?>
                <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($vt['name']) ?>" style="max-width:90px; max-height:50px; object-fit:contain;" class="rounded border p-1 bg-white">
              <?php else: ?>
                <span class="text-muted small">No Image</span>
              <?php endif; ?>
            </td>

            <!-- Name -->
            <td class="fw-bold fs-6 text-dark"><?= htmlspecialchars($vt['name']) ?></td>

            <!-- Driver -->
            <td class="text-muted">All</td>

            <!-- Services -->
            <td class="text-muted">All</td>

            <!-- Hourly rate -->
            <td class="fw-semibold">&pound;<?= number_format($vt['hourly_rate'] ?? 0, 0) ?></td>

            <!-- Capacity list -->
            <td>
              <div class="small text-muted lh-sm" style="font-size: 0.82rem;">
                <div>Passengers: <strong class="text-dark"><?= intval($vt['passengers']) ?></strong></div>
                <div>Luggage: <strong class="text-dark"><?= intval($vt['luggage']) ?></strong></div>
                <div>Hand luggage: <strong class="text-dark"><?= intval($vt['hand_luggage']) ?></strong></div>
                <div>Booster seats: <strong class="text-dark"><?= intval($vt['baby_seats']) ?></strong></div>
                <div>Child seats: <strong class="text-dark"><?= intval($vt['child_seats']) ?></strong></div>
                <div>Infant seats: <strong class="text-dark"><?= intval($vt['infant_seats']) ?></strong></div>
                <?php if (!empty($vt['wheelchair'])): ?>
                <div>Wheelchairs: <strong class="text-dark"><?= intval($vt['wheelchair']) ?></strong></div>
                <?php endif; ?>
              </div>
            </td>

            <!-- Booking option -->
            <td><?= htmlspecialchars($vt['disable_info'] ?: 'Normal booking') ?></td>

            <!-- Default -->
            <td>
              <?php if (!empty($vt['default'])): ?>
                <span class="badge bg-primary px-2 py-1">Default</span>
              <?php endif; ?>
            </td>

            <!-- Active -->
            <td>
              <?php if (!empty($vt['published'])): ?>
                <span class="fw-bold text-success">Yes</span>
              <?php else: ?>
                <span class="fw-bold text-muted">No</span>
              <?php endif; ?>
            </td>

            <!-- Ordering -->
            <td class="fw-semibold"><?= intval($vt['ordering']) ?></td>

            <!-- Display -->
            <td class="pe-3">
              <?php 
              $disp = 'Frontend & Backend';
              if (intval($vt['is_backend']) === 1) $disp = 'Backend Only';
              elseif (intval($vt['is_backend']) === 2) $disp = 'Frontend Only';
              echo htmlspecialchars($disp);
              ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Form (Add New / Edit) -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <form method="POST" enctype="multipart/form-data" id="vehicleForm">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" id="vt_id" value="0">
        <input type="hidden" name="current_image" id="vt_current_image" value="">

        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold" id="vehicleModalLabel">Add new</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Name -->
          <div class="mb-3">
            <label class="form-label fw-semibold text-muted small">Name</label>
            <input type="text" name="name" id="vt_name" class="form-control" placeholder="Name" required>
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label fw-semibold text-muted small">Description</label>
            <textarea name="description" id="vt_description" class="form-control" rows="2" placeholder="Description"></textarea>
          </div>

          <!-- Image Selection -->
          <div class="mb-3">
            <div class="form-check form-check-inline me-4">
              <input class="form-check-input" type="radio" name="image_type" id="img_type_gallery" value="0" checked onchange="toggleImageType(0)">
              <label class="form-check-label fw-semibold" for="img_type_gallery">Gallery</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="image_type" id="img_type_upload" value="1" onchange="toggleImageType(1)">
              <label class="form-check-label fw-semibold" for="img_type_upload">Upload</label>
            </div>
          </div>

          <!-- Gallery Dropdown -->
          <div class="mb-3" id="gallery_container">
            <label class="form-label fw-semibold text-muted small">Choose image</label>
            <select name="image_gallery" id="vt_image_gallery" class="form-select">
              <option value="">-- Choose image --</option>
              <?php foreach ($gallery_images as $gimg): ?>
                <option value="<?= htmlspecialchars($gimg) ?>"><?= htmlspecialchars($gimg) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Upload Input -->
          <div class="mb-3" id="upload_container" style="display:none;">
            <label class="form-label fw-semibold text-muted small">Upload image</label>
            <input type="file" name="image_upload" id="vt_image_upload" class="form-control" accept="image/*">
            <div class="form-text text-muted">Please upload an image in PNG format (approx 200x100 px).</div>
          </div>

          <!-- Stepper Numeric Inputs -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small">Passengers</label>
              <div class="input-group">
                <input type="number" name="passengers" id="vt_passengers" class="form-control" value="0" min="0">
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_passengers',1)">+</button>
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_passengers',-1)">-</button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small">Luggage</label>
              <div class="input-group">
                <input type="number" name="luggage" id="vt_luggage" class="form-control" value="0" min="0">
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_luggage',1)">+</button>
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_luggage',-1)">-</button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small">Hand luggage</label>
              <div class="input-group">
                <input type="number" name="hand_luggage" id="vt_hand_luggage" class="form-control" value="0" min="0">
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_hand_luggage',1)">+</button>
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_hand_luggage',-1)">-</button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small">Wheelchairs</label>
              <div class="input-group">
                <input type="number" name="wheelchair" id="vt_wheelchair" class="form-control" value="0" min="0">
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_wheelchair',1)">+</button>
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_wheelchair',-1)">-</button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small">Booster seats</label>
              <div class="input-group">
                <input type="number" name="baby_seats" id="vt_baby_seats" class="form-control" value="0" min="0">
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_baby_seats',1)">+</button>
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_baby_seats',-1)">-</button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small">Child seats</label>
              <div class="input-group">
                <input type="number" name="child_seats" id="vt_child_seats" class="form-control" value="0" min="0">
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_child_seats',1)">+</button>
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_child_seats',-1)">-</button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small">Infant seats</label>
              <div class="input-group">
                <input type="number" name="infant_seats" id="vt_infant_seats" class="form-control" value="0" min="0">
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_infant_seats',1)">+</button>
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_infant_seats',-1)">-</button>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small">Ordering</label>
              <div class="input-group">
                <input type="number" name="ordering" id="vt_ordering" class="form-control" value="0" min="0">
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_ordering',1)">+</button>
                <button type="button" class="btn btn-outline-secondary" onclick="stepVal('vt_ordering',-1)">-</button>
              </div>
            </div>
          </div>

          <!-- Driver -->
          <div class="mb-3">
            <label class="form-label fw-semibold text-muted small">Driver</label>
            <select name="driver" id="vt_driver" class="form-select">
              <option value="Unassigned">Unassigned</option>
              <option value="All">All</option>
            </select>
          </div>

          <!-- Booking Option -->
          <div class="mb-3">
            <label class="form-label fw-semibold text-muted small">Booking option</label>
            <select name="booking_option" id="vt_booking_option" class="form-select">
              <option value="Normal booking">Normal booking</option>
            </select>
          </div>

          <!-- Checkboxes: Default & Active -->
          <div class="mb-3 d-flex gap-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_default" id="vt_default" value="1">
              <label class="form-check-label fw-semibold" for="vt_default">Default</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="published" id="vt_published" value="1" checked>
              <label class="form-check-label fw-semibold" for="vt_published">Active</label>
            </div>
          </div>

          <!-- Display -->
          <div class="mb-3">
            <label class="form-label fw-semibold text-muted small">Display</label>
            <select name="is_backend" id="vt_is_backend" class="form-select">
              <option value="0">Frontend &amp; Backend</option>
              <option value="1">Backend Only</option>
              <option value="2">Frontend Only</option>
            </select>
          </div>
        </div>

        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success px-4 fw-bold" id="vt_submit_btn"><i class="fas fa-plus me-1"></i> Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleImageType(type) {
  if (type === 0) {
    document.getElementById('gallery_container').style.display = 'block';
    document.getElementById('upload_container').style.display = 'none';
  } else {
    document.getElementById('gallery_container').style.display = 'none';
    document.getElementById('upload_container').style.display = 'block';
  }
}

function stepVal(id, delta) {
  var el = document.getElementById(id);
  var val = parseInt(el.value || 0) + delta;
  if (val < 0) val = 0;
  el.value = val;
}

function openVehicleModal() {
  document.getElementById('vehicleForm').reset();
  document.getElementById('vt_id').value = '0';
  document.getElementById('vt_current_image').value = '';
  document.getElementById('vehicleModalLabel').innerText = 'Add new';
  document.getElementById('vt_submit_btn').innerHTML = '<i class="fas fa-plus me-1"></i> Add';
  document.getElementById('img_type_gallery').checked = true;
  toggleImageType(0);
  document.getElementById('vt_published').checked = true;
  var el = document.getElementById('vehicleModal');
  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
    modal.show();
  } else if (typeof $ !== 'undefined') {
    $(el).modal('show');
  }
}

function editVehicleModal(data) {
  document.getElementById('vehicleForm').reset();
  document.getElementById('vt_id').value = data.id || 0;
  document.getElementById('vt_current_image').value = data.image || '';
  document.getElementById('vt_name').value = data.name || '';
  document.getElementById('vt_description').value = data.description || '';
  document.getElementById('vt_passengers').value = data.passengers || 0;
  document.getElementById('vt_luggage').value = data.luggage || 0;
  document.getElementById('vt_hand_luggage').value = data.hand_luggage || 0;
  document.getElementById('vt_wheelchair').value = data.wheelchair || 0;
  document.getElementById('vt_baby_seats').value = data.baby_seats || 0;
  document.getElementById('vt_child_seats').value = data.child_seats || 0;
  document.getElementById('vt_infant_seats').value = data.infant_seats || 0;
  document.getElementById('vt_ordering').value = data.ordering || 0;
  document.getElementById('vt_booking_option').value = data.disable_info || 'Normal booking';
  document.getElementById('vt_default').checked = (parseInt(data.default) === 1);
  document.getElementById('vt_published').checked = (parseInt(data.published) === 1);
  document.getElementById('vt_is_backend').value = data.is_backend || 0;

  if (data.image) {
    document.getElementById('vt_image_gallery').value = data.image;
  }

  document.getElementById('vehicleModalLabel').innerText = 'Edit vehicle type';
  document.getElementById('vt_submit_btn').innerHTML = '<i class="fas fa-save me-1"></i> Save';
  document.getElementById('img_type_gallery').checked = true;
  toggleImageType(0);

  var el = document.getElementById('vehicleModal');
  if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
    modal.show();
  } else if (typeof $ !== 'undefined') {
    $(el).modal('show');
  }
}
</script>

<?php require_once 'footer.php'; ?>