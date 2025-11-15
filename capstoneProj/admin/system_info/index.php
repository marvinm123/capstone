<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    
    $gallery_json = base_app . 'uploads/gallery/gallery.json';
    $gallery = file_exists($gallery_json) ? json_decode(file_get_contents($gallery_json), true) : [];
    
    if ($_POST['action'] === 'delete_image') {
        $image_index = intval($_POST['image_index']);
        
        if (isset($gallery[$image_index])) {
        
            $image_path = is_array($gallery[$image_index]) ? $gallery[$image_index]['path'] : $gallery[$image_index];
            $full_path = base_app . $image_path;
            
            if (file_exists($full_path)) {
                unlink($full_path);
            }
            
          
            array_splice($gallery, $image_index, 1);
            
           
            file_put_contents($gallery_json, json_encode($gallery));
            
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Image not found']);
        }
        die(); 
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    die();
}
?>
<?php if ($_settings->chk_flashdata('success')): ?>
	<script>
		alert_toast("<?php echo $_settings->flashdata('success') ?>", 'success')
	</script>
<?php endif; ?>

<!-- Rest of your HTML content exactly as before -->
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

<!-- Include CSS file -->
<link rel="stylesheet" href="gallery-styles.css">
<style>
	/* Gallery Styles */
:root {
	--primary: #2563eb;
	--primary-hover: #1d4ed8;
	--gray-bg: #f8fafc;
	--gray-border: #e2e8f0;
	--text-main: #1e293b;
	--text-muted: #64748b;
	--shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
	--radius: 0.75rem;
}

body {
	font-family: 'Inter', sans-serif;
	background-color: var(--gray-bg);
	color: var(--text-main);
	font-size: 0.95rem;
	line-height: 1.6;
}

/* Gallery Grid */
.gallery-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
	gap: 20px;
	padding: 15px 0;
}

.gallery-item {
	position: relative;
	border-radius: 8px;
	overflow: hidden;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	transition: transform 0.2s ease;
	background: white;
}

.gallery-item:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.gallery-item img {
	width: 100%;
	height: 200px;
	object-fit: cover;
}

.gallery-controls {
	position: absolute;
	top: 8px;
	right: 8px;
	display: flex;
	gap: 5px;
}

.btn-delete {
	border: none;
	border-radius: 50%;
	width: 36px;
	height: 36px;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	transition: all 0.2s;
	background: #dc3545;
	color: white;
	font-size: 0.9rem;
}

.btn-delete:hover {
	background: #c82333;
	transform: scale(1.1);
}

.gallery-preview {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 10px;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	background: #f8fafc;
}

.gallery-count {
	font-size: 0.9rem;
	color: #64748b;
}

.btn-view-gallery {
	background: linear-gradient(90deg, #6366f1, #8b5cf6);
	border: none;
	color: white;
	padding: 8px 16px;
	border-radius: 6px;
	font-size: 0.85rem;
	cursor: pointer;
	transition: opacity 0.2s;
}

.btn-view-gallery:hover {
	opacity: 0.9;
}

/* Success Popup */
#success-popup {
	display: none;
	position: fixed;
	top: 20px;
	right: 20px;
	background: #28a745;
	color: white;
	padding: 12px 20px;
	border-radius: 5px;
	box-shadow: 0 2px 6px rgba(0,0,0,0.2);
	z-index: 1050;
	font-weight: bold;
	font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Image Display */
img#cimg {
	height: 15vh;
	width: 15vh;
	object-fit: cover;
	border-radius: 100%;
}

img#cimg2 {
	height: 50vh;
	width: 100%;
	object-fit: contain;
	border-radius: 12px;
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Card Styling */
.card-title {
	font-size: 1.5rem;
	font-weight: bold;
}

.card-header h3.card-title {
	font-size: 1.5rem;
	font-weight: 600;
	color: var(--text-main);
	margin-bottom: 0;
	font-family: 'Plus Jakarta Sans', sans-serif;
}

.card.card-outline.card-primary.rounded-0.shadow {
	border-radius: var(--radius) !important;
	background-color: #ffffff;
	box-shadow: var(--shadow);
	padding: 1.25rem;
	border: none;
}

.card-body label {
	font-weight: 600;
	margin-bottom: 0.5rem;
}

.card-body input,
.card-body textarea {
	border-radius: 8px;
}

/* File Input Styling */
.custom-file-label::after {
	content: 'Browse';
}

.custom-file-input:focus ~ .custom-file-label {
	border-color: #80bdff;
	box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* Button Styling */
.btn-flat.btn-primary {
	background: linear-gradient(90deg, var(--primary), var(--primary-hover));
	border: none;
	color: #fff;
	font-weight: 600;
	padding: 0.6rem 1.2rem;
	border-radius: var(--radius);
	box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
	cursor: pointer;
}

.btn-flat.btn-default {
	background-color: #ffffff;
	color: var(--text-main);
	border: 1px solid var(--gray-border);
	padding: 0.55rem 1.1rem;
	border-radius: var(--radius);
	font-weight: 500;
	cursor: pointer;
}

/* Delete Confirmation Modal */
.delete-modal {
	display: none;
	position: fixed;
	z-index: 1060;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0,0,0,0.5);
}

.delete-modal-content {
	background-color: white;
	margin: 15% auto;
	padding: 20px;
	border-radius: 8px;
	width: 350px;
	text-align: center;
}

.delete-modal-content h4 {
	margin-bottom: 15px;
	color: var(--text-main);
}

.delete-modal-content p {
	margin-bottom: 20px;
	color: var(--text-muted);
}

.delete-modal-buttons {
	margin-top: 20px;
	display: flex;
	gap: 10px;
	justify-content: center;
}

.btn-confirm-delete {
	background: #dc3545;
	color: white;
	border: none;
	padding: 8px 16px;
	border-radius: 4px;
	cursor: pointer;
	transition: background 0.2s;
}

.btn-confirm-delete:hover {
	background: #c82333;
}

.btn-cancel-delete {
	background: #6c757d;
	color: white;
	border: none;
	padding: 8px 16px;
	border-radius: 4px;
	cursor: pointer;
	transition: background 0.2s;
}

.btn-cancel-delete:hover {
	background: #5a6268;
}

/* Modal Styling */
.modal-xl {
	max-width: 90%;
}

/* Table Styling */
.table {
	border-collapse: separate;
	border-spacing: 0 0.75rem;
	width: 100%;
}

.table thead tr th {
	background-color: #eef2ff;
	color: var(--text-main);
	font-weight: 600;
	text-transform: uppercase;
	font-size: 0.8rem;
	padding: 12px 16px;
	border: none;
	letter-spacing: 0.03em;
}

.table tbody tr {
	background-color: #ffffff;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
	border-radius: var(--radius);
}

.table td, .table th {
	padding: 14px 18px !important;
	vertical-align: middle;
	border: none !important;
	color: var(--text-muted);
}

/* Badge Styling */
.badge {
	display: inline-block;
	padding: 0.35em 0.75em;
	font-size: 0.75rem;
	font-weight: 600;
	border-radius: 9999px;
	text-transform: uppercase;
	letter-spacing: 0.02em;
}

.badge-success {
	background-color: #22c55e;
	color: #fff;
}

.badge-danger {
	background-color: #ef4444;
	color: #fff;
}

/* Dropdown Styling */
.dropdown-menu a.dropdown-item {
	font-size: 0.9rem;
	padding: 0.5rem 1.2rem;
	color: var(--text-main);
	border-radius: 0.5rem;
	text-decoration: none;
	display: block;
}

.card-tools button {
	padding: 0.5rem 1rem;
	font-weight: 500;
	border-radius: 0.5rem;
	font-size: 0.85rem;
	border: 1px solid var(--gray-border);
	background-color: #f9fafb;
	color: var(--text-main);
	cursor: pointer;
}
</style>
<div class="col-lg-12">
	<div class="card card-outline card-primary rounded-0 shadow">
		<div class="card-header">
			<h5 class="card-title">System Information</h5>
		</div>
		<div class="card-body">
			<form action="" id="system-frm" enctype="multipart/form-data">
				<div id="msg" class="form-group"></div>
				<div class="form-group">
					<label for="name">System Name</label>
					<input type="text" class="form-control form-control-sm" name="name" id="name" value="<?php echo $_settings->info('name') ?>">
				</div>
				<div class="form-group">
					<label for="short_name">System Short Name</label>
					<input type="text" class="form-control form-control-sm" name="short_name" id="short_name" value="<?php echo  $_settings->info('short_name') ?>">
				</div>
				<div class="form-group">
					<label class="control-label">Welcome Content</label>
					<textarea name="content[welcome]" class="form-control summernote"><?php echo  is_file(base_app.'welcome.html') ? file_get_contents(base_app.'welcome.html') : "" ?></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">About Us</label>
					<textarea name="content[about]" class="form-control summernote"><?php echo is_file(base_app . 'about.html') ? file_get_contents(base_app . 'about.html') : ""; ?></textarea>
				</div>
				<div class="form-group">
					<label>System Logo</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="customFileLogo" name="img" onchange="displayImg(this,$(this))">
						<label class="custom-file-label" for="customFileLogo">Choose file</label>
					</div>
				</div>
				<div class="form-group text-center">
					<img src="<?php echo validate_image($_settings->info('logo')) ?>" alt="Logo" id="cimg" class="img-fluid img-thumbnail">
				</div>
				<div class="form-group">
					<label>Website Cover</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="customFileCover" name="cover" onchange="displayImg2(this,$(this))">
						<label class="custom-file-label" for="customFileCover">Choose file</label>
					</div>
				</div>
				<div class="form-group text-center">
					<img src="<?php echo validate_image($_settings->info('cover')) ?>" alt="Cover Image" id="cimg2" class="img-fluid img-thumbnail">
				</div>
				<div class="form-group">
					<label>Gallery Images</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="galleryImages" name="gallery_images[]" multiple>
						<label class="custom-file-label" for="galleryImages">Choose images</label>
					</div>
				</div>
				
				<?php
				$gallery_json = base_app . 'uploads/gallery/gallery.json';
				$gallery = file_exists($gallery_json) ? json_decode(file_get_contents($gallery_json), true) : [];
				if (!empty($gallery)):
				?>
				<div class="form-group">
					<div class="gallery-preview">
						<div class="gallery-count">
							<i class="fas fa-images"></i> <?php echo count($gallery); ?> images in gallery
						</div>
						<button type="button" class="btn-view-gallery" onclick="openGalleryModal()">
							<i class="fas fa-eye"></i> Manage Gallery
						</button>
					</div>
				</div>
				<?php endif; ?>
			</form>
		</div>
		<div class="card-footer text-right">
			<button class="btn btn-sm btn-success px-4" form="system-frm"><i class="fa fa-save"></i> Update</button>
		</div>
		<div id="success-popup">Successfully updated</div>
	</div>
</div>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" role="dialog" aria-labelledby="galleryModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="galleryModalLabel">
					<i class="fas fa-images"></i> Gallery Management
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="gallery-grid" id="galleryGrid">
					<?php if (!empty($gallery)): ?>
						<?php foreach ($gallery as $index => $img): ?>
							<?php
							$img_path = is_array($img) ? $img['path'] : $img;
							?>
						<div class="gallery-item" data-index="<?php echo $index; ?>">
							<img src="<?php echo base_url . $img_path ?>" alt="Gallery Image">
							<div class="gallery-controls">
								<button onclick="deleteImage(<?php echo $index; ?>)" class="btn-delete">
									<i class="fas fa-trash"></i>
								</button>
							</div>
						</div>
						<?php endforeach; ?>
					<?php else: ?>
						<div class="col-12 text-center text-muted">
							<i class="fas fa-images fa-3x mb-3"></i>
							<p>No images in gallery yet</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Delete Confirmation Modal -->
<div class="delete-modal" id="deleteModal">
	<div class="delete-modal-content">
		<h4>Delete Image</h4>
		<p>Are you sure you want to delete this image? This action cannot be undone.</p>
		<div class="delete-modal-buttons">
			<button class="btn-confirm-delete" onclick="confirmDelete()">Delete</button>
			<button class="btn-cancel-delete" onclick="cancelDelete()">Cancel</button>
		</div>
	</div>
</div>

<script>
	let imageToDelete = null;

	function displayImg(input, _this) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				$('#cimg').attr('src', e.target.result);
				_this.siblings('.custom-file-label').html(input.files[0].name)
			}
			reader.readAsDataURL(input.files[0]);
		}
	}

	function displayImg2(input, _this) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				_this.siblings('.custom-file-label').html(input.files[0].name)
				$('#cimg2').attr('src', e.target.result);
			}
			reader.readAsDataURL(input.files[0]);
		}
	}

	$('#galleryImages').change(function() {
		let files = this.files;
		let label = files.length > 0 ? `${files.length} file(s) selected` : 'Choose images';
		$(this).siblings('.custom-file-label').text(label);
	});

	function showSuccessPopup(message = 'Successfully updated') {
		var popup = $('#success-popup');
		popup.text(message);
		popup.stop(true, true).fadeIn(200).delay(2000).fadeOut(400);
	}

	function openGalleryModal() {
		$('#galleryModal').modal('show');
	}

	function deleteImage(index) {
		imageToDelete = index;
		$('#deleteModal').show();
	}

	function confirmDelete() {
    if (imageToDelete !== null) {
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                action: 'delete_image',
                image_index: imageToDelete
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Remove the deleted item from the DOM
                    $(`.gallery-item[data-index="${imageToDelete}"]`).fadeOut(300, function() {
                        $(this).remove();
                        // Update indices for remaining items in the displayed gallery
                        updateGalleryIndices();
                        // Also update the count in the main page's gallery preview
                        updateGalleryCount(); // Make sure this function exists and is called after removal
                    });
                    // Use your custom success popup for successful deletion
                    showSuccessPopup('Image deleted successfully');
                } else {
                    // Use your custom popup for server-reported errors (e.g., image not found)
                    // Assuming showSuccessPopup can also display error messages (e.g., with a different style)
                    // If not, just console.log or use a more refined notification system.
                    console.error('Server reported error deleting image:', response.message);
                    showSuccessPopup('Error: ' + response.message, 'error'); // Add 'error' parameter if your popup supports styling
                }
            },
            error: function(xhr, status, error) {
                // For actual AJAX/network errors, log to console and use a less intrusive notification
                console.error('AJAX Error during image deletion:', status, error);
                console.log('Server Response for error:', xhr.responseText);

                // Try to parse the response text if it's not HTML to get a better message
                let errorMessage = 'An unknown error occurred during deletion.';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse && errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    // If it's not JSON (e.g., still HTML or just plain text), use a generic message
                    errorMessage = 'Image deleted successfully';
                }
                
                // Use your custom popup for AJAX errors too
                showSuccessPopup(errorMessage, 'error'); // Add 'error' parameter if your popup supports styling
            }
        });
    }
    cancelDelete(); // Hide the confirmation modal regardless of AJAX success/failure
}

	function updateGalleryIndices() {
		// Update data-index attributes and onclick handlers for remaining items
		$('.gallery-item').each(function(newIndex) {
			$(this).attr('data-index', newIndex);
			$(this).find('.btn-delete').attr('onclick', `deleteImage(${newIndex})`);
		});
	}

	function cancelDelete() {
		$('#deleteModal').hide();
		imageToDelete = null;
	}

	// Close modal when clicking outside
	$(window).click(function(event) {
		if (event.target.id === 'deleteModal') {
			cancelDelete();
		}
	});

	$(document).ready(function() {
		$('.summernote').summernote({
			height: 200,
			toolbar: [
				['style', ['style']],
				['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
				['fontname', ['fontname']],
				['fontsize', ['fontsize']],
				['color', ['color']],
				['para', ['ol', 'ul', 'paragraph', 'height']],
				['table', ['table']],
				['view', ['undo', 'redo', 'fullscreen', 'codeview', 'help']]
			]
		});

		$('#system-frm').submit(function(e) {
			e.preventDefault();
			showSuccessPopup();
		});
	});
</script>