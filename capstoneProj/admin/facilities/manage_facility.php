<?php
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `facility_list` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=stripslashes($v);
        }
    }
}
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<style>
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

    .card-header h3.card-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .btn-flat.btn-primary, .btn.btn-primary {
        background: linear-gradient(90deg, var(--primary), var(--primary-hover));
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: var(--radius);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        cursor: pointer;
    }

    .btn.btn-outline-secondary {
        border: 1px solid var(--gray-border);
        background: #fff;
        color: var(--text-main);
    }

    .card.card-outline {
        border-radius: var(--radius);
        background-color: #fff;
        box-shadow: var(--shadow);
        border: none;
        padding: 1.2rem;
    }

    .table {
        border-collapse: separate !important;
        border-spacing: 0 1rem !important;
        width: 100%;
    }

    .table thead tr th {
        background-color: #eef2ff;
        color: var(--text-main);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 12px 16px;
        border: none !important;
        letter-spacing: 0.03em;
    }

    .table tbody tr td {
        background-color: #fff;
        padding: 14px 18px !important;
        border: none !important;
        color: var(--text-muted);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border-radius: var(--radius);
        vertical-align: middle;
    }

    .badge {
        display: inline-block;
        padding: 0.35em 0.75em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .badge-success { background-color: #22c55e; color: #fff; }
    .badge-danger { background-color: #ef4444; color: #fff; }
    .badge-new { 
        background-color: #ffc107; /* yellow */
        color: #212529;
        margin-left: 0.5rem;
        font-weight: 600;
        font-size: 0.7rem;
        vertical-align: middle;
    }

    .dropdown-menu a.dropdown-item {
        font-size: 0.9rem;
        padding: 0.5rem 1.2rem;
        color: var(--text-main);
        border-radius: 0.5rem;
        text-decoration: none;
        display: block;
    }

    .truncate-1 {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Legend Styling */
    #dynamicLegend .custom-legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .custom-legend-color {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 0.6rem;
    }
	#cimg{
		min-width: 34vw;
		min-height: 25vh;
		max-height: 35vh;
		max-width: 100%;
		object-fit:scale-down;
		object-position:center center;
	}
</style>
<div class="card card-outline ">
	<div class="card-header">
		<h3 class="card-title"><?php echo isset($id) ? "Update ": "Create New " ?> Facility</h3>
	</div>
	<div class="card-body">
		<form action="" id="facility-form" enctype="multipart/form-data">
			<input type="hidden" name ="id" value="<?php echo isset($id) ? $id : '' ?>">
            <div class="form-group">
				<label for="category_id" class="control-label">Category</label>
                <select name="category_id" id="category_id" class="custom-select select2">
                    <option value="" <?= !isset($category_id) ? "selected" : "" ?> disabled></option>
                    <?php 
                    $categorys = $conn->query("SELECT * FROM category_list where delete_flag = 0 ".(isset($category_id) ? " or id = '{$category_id}'" : "")." order by `name` asc ");
                    while($row= $categorys->fetch_assoc()):
                    ?>
                    <option value="<?= $row['id'] ?>" <?= isset($category_id) && $category_id == $row['id'] ? "selected" : "" ?>><?= $row['name'] ?> <?= $row['delete_flag'] == 1 ? "<small>Deleted</small>" : "" ?></option>
                    <?php endwhile; ?>
                </select>
			</div>
            <div class="form-group">
				<label for="name" class="control-label">Name</label>
                <input name="name" id="name" type="text" class="form-control rounded-0" value="<?php echo isset($name) ? $name : ''; ?>" required>
			</div>
			<div class="form-group">
				<label for="description" class="control-label">Description</label>
                <textarea name="description" id="description" rows="5" class="form-control rounded-0 summernote" required><?php echo isset($description) ? $description : ''; ?></textarea>
			</div>
			<div class="form-group">
				<label for="price" class="control-label">Rent Price</label>
                <input name="price" id="price" type="text" class="form-control rounded-0" value="<?php echo isset($price) ? $price : ''; ?>" required />
			</div>
			<div class="form-group col-md-6">
				<label for="" class="control-label">Facility's Image</label>
				<div class="custom-file">
	              <input type="file" name="images[]" multiple class="custom-file-input rounded-circle" id="customFile" name="img" onchange="displayImg(this,$(this))">
	              <label class="custom-file-label" for="customFile">Choose file</label>
	            </div>
			</div>
			
			<div class="form-group col-md-12 d-flex flex-wrap justify-content-start" id="image-preview">
				<img src="<?php echo validate_image(isset($image_path) ? $image_path : "") ?>" alt="" id="cimg" class="img-fluid img-thumbnail bg-gradient-gray">
			</div>
            <div class="form-group">
				<label for="status" class="control-label">Status</label>
                <select name="status" id="status" class="custom-select selevt">
                <option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?php echo isset($status) && $status == 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
			</div>
		</form>
	</div>
	<div class="card-footer">
		<button class="btn btn-flat btn-primary" form="facility-form">Save</button>
		<a class="btn btn-flat btn-default" href="?page=facilities">Cancel</a>
	</div>
</div>
<script>
	window.displayImg = function(input,_this) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#cimg').attr('src', e.target.result);  // Update preview
            _this.siblings('.custom-file-label').html(input.files[0].name);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        // Ensure image URL persists after upload
        var savedImage = "<?php echo isset($image_path) ? $image_path : '' ?>"; 
        if(savedImage) {
            $('#cimg').attr('src', savedImage);
        } else {
            $('#cimg').attr('src', 'uploads/facility'); // Default image if none is set
        }
        _this.siblings('.custom-file-label').html("Choose file");
    }
}


	$(document).ready(function(){
		$('.select2').select2({
			width:'100%',
			placeholder:"Please Select Here"
		})
		$('.pass_view').click(function(){
			var group = $(this).closest('.input-group');
			var type = group.find('input').attr('type')
			if(type == 'password'){
				group.find('input').attr('type','text').focus()
				$(this).html('<i class="fa fa-eye"></i>')
			}else{
				group.find('input').attr('type','password').focus()
				$(this).html('<i class="fa fa-eye-slash"></i>')
			}
		})
		$('#facility-form').submit(function(e){
			e.preventDefault();
            var _this = $(this)
			 $('.err-msg').remove();
			start_loader();
			$.ajax({
				url:_base_url_+"classes/Master.php?f=save_facility",
				data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
				error:err=>{
					console.log(err)
					alert_toast("An error occured",'error');
					end_loader();
				},
				success:function(resp){
					if(typeof resp =='object' && resp.status == 'success'){
						location.href = "./?page=facilities/view_facility&id="+resp.id;
					}else if(resp.status == 'failed' && !!resp.msg){
                        var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                            $("html, body").animate({ scrollTop: _this.closest('.card').offset().top }, "fast");
                            end_loader()
                    }else{
						alert_toast("An error occured",'error');
						end_loader();
                        console.log(resp)
					}
				}
			})
		})

        $('.summernote').summernote({
		        height: "40vh",
		        toolbar: [
		            [ 'style', [ 'style' ] ],
		            [ 'font', [ 'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear'] ],
		            [ 'fontname', [ 'fontname' ] ],
		            [ 'fontsize', [ 'fontsize' ] ],
		            [ 'color', [ 'color' ] ],
		            [ 'para', [ 'ol', 'ul', 'paragraph', 'height' ] ],
		            [ 'table', [ 'table' ] ],
		            [ 'view', [ 'undo', 'redo', 'fullscreen', 'codeview', 'help' ] ]
		        ]
		    })
	})
</script>