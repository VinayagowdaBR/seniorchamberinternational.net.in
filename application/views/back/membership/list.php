<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">Membership Types</h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="demo-pli-home"></i></a></li>
            <li class="active">Membership Types</li>
        </ol>
    </div>

    <div id="page-content">
        <div class="panel">
            <?php if (!empty($success_alert)) { ?>
            <div class="alert alert-success alert-dismissible">
                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                <?=$success_alert?>
            </div>
            <?php } ?>
            
            <?php if (!empty($danger_alert)) { ?>
            <div class="alert alert-danger alert-dismissible">
                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                <?=$danger_alert?>
            </div>
            <?php } ?>
            
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="panel-title">All Membership Types</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="<?=base_url()?>admin/membership_management/add" 
                           class="btn btn-primary btn-labeled fa fa-plus-circle">
                            Add New
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="panel-body">
                <table id="membership_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width:50px;">ID</th>
                            <th>Name</th>
                            <th style="width:80px;">Value</th>
                            <th>Slug</th>
                            <th style="width:80px;">Status</th>
                            <th style="width:150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var table = $('#membership_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?=base_url()?>admin/membership_management/list_data",
            "type": "POST"
        },
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4, "orderable": false },
            { "data": 5, "orderable": false }
        ],
        "order": [[0, 'asc']],
        "pageLength": 25,
        "language": {
            "processing": "Loading data..."
        }
    });
});

// Confirm delete function
function confirm_modal(delete_url) {
    if (confirm('Are you sure you want to delete this membership type?')) {
        window.location.href = delete_url;
    }
}
</script>
