<?php
require_once('./../../config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * FROM `category_list` WHERE id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k = $v;
        }
    }
}
?>
<style>
    #uni_modal .modal-footer {
        display: none;
    }
    .category-details dl {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 0;
    }
    .category-details dt, .category-details dd {
        width: 50%;
        margin-bottom: 1rem;
    }
    .category-details dt {
        font-weight: 600;
        color: #374151; /* gray-700 */
    }
    .category-details dd {
        margin-left: 0;
        padding-left: 1rem;
        color: #4b5563; /* gray-600 */
        word-break: break-word;
    }
    .badge {
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.4em 1em;
        border-radius: 9999px;
        display: inline-block;
        min-width: 90px;
        text-align: center;
    }
    .btn-flat {
        border-radius: 0.375rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        cursor: pointer;
        border: none;
        transition: background-color 0.3s ease;
    }
    .btn-flat.bg-gradient-dark {
        background: #374151;
        color: #fff;
    }
    .btn-flat.bg-gradient-dark:hover {
        background: #1f2937;
    }
    @media (max-width: 576px) {
        .category-details dt, .category-details dd {
            width: 100%;
        }
    }
</style>

<div class="container-fluid category-details">
    <dl>
        <dt>Name</dt>
        <dd><?= isset($name) ? htmlspecialchars($name) : "" ?></dd>

        <dt>Description</dt>
        <dd><?= isset($description) ? nl2br(htmlspecialchars($description)) : '' ?></dd>

        <dt>Status</dt>
        <dd>
            <?php if(isset($status) && $status == 1): ?>
                <span class="badge badge-success bg-gradient-success px-3 rounded-pill">Active</span>
            <?php else: ?>
                <span class="badge badge-danger bg-gradient-danger px-3 rounded-pill">Inactive</span>
            <?php endif; ?>
        </dd>
    </dl>
    <div class="text-right">
        <button class="btn-flat bg-gradient-dark" type="button" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
    </div>
</div>
