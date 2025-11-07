<!--CONTENT CONTAINER-->
<div id="content-container">
    <div id="page-head">
        <!--Page Title-->
        <div id="page-title">
            <h1 class="page-header text-overflow">Membership Types</h1>
        </div>
        <!--End page title-->

        <!--Breadcrumb-->
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="demo-pli-home"></i></a></li>
            <li class="active">Membership Types</li>
        </ol>
        <!--End breadcrumb-->
    </div>

    <!--Page content-->
    <div id="page-content">
        <!-- Basic Data Tables -->
        <div class="panel">
            <?php if (!empty($success_alert)) { ?>
            <div class="alert alert-success" id="success_alert" style="display: block;">
                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                <?=$success_alert?>
            </div>
            <?php } ?>
            
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="panel-title">All Membership Types</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="<?=base_url()?>admin/membership_management/add" class="btn btn-primary btn-labeled fa fa-plus-circle">
                            Add New
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="panel-body" style="padding: 15px 20px 0px !important;">
                <table id="membership_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">Name</th>
                            <th width="10%">Value</th>
                            <th width="20%">Slug</th>
                            <th width="10%">Status</th>
                            <th width="15%" data-sortable="false">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <!--End Striped Table -->
    </div>
    <!--End page content-->
</div>

<script type="text/javascript">
$(document).ready(function() {
    var table = $('#membership_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?=base_url()?>admin/membership_management/list_data",
            "type": "POST",
            "data": {
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            }
        },
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5, "orderable": false }
        ],
        "order": [[0, 'asc']],
        "drawCallback": function(settings) {
            $('.add-tooltip').tooltip();
        }
    });
    
    setTimeout(function() {
        $('#success_alert').fadeOut('fast');
    }, 5000);
});
</script>
