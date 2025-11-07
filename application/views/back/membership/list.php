<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
    <div id="page-head">
        <!--Page Title-->
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <div id="page-title">
            <h1 class="page-header text-overflow"><?php echo translate('membership_management')?></h1>
        </div>
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <!--End page title-->

        <!--Breadcrumb-->
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><?php echo translate('home')?></a></li>
            <li class="active"><?php echo translate('membership_types')?></li>
        </ol>
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <!--End breadcrumb-->
    </div>

    <!--Page content-->
    <!--===================================================-->
    <div id="page-content">
        <!-- Basic Data Tables -->
        <!--===================================================-->
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
                        <h3 class="panel-title"><?php echo translate('membership_types')?></h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="<?=base_url()?>admin/membership_management/add" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> <?php echo translate('add_new')?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="panel-body" style="padding: 15px 20px 0px !important;">
                <table id="membership_table" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th width="5%"><?php echo translate('id')?></th>
                            <th width="20%"><?php echo translate('name')?></th>
                            <th width="20%"><?php echo translate('slug')?></th>
                            <th width="10%"><?php echo translate('value')?></th>
                            <th width="10%"><?php echo translate('status')?></th>
                            <th width="15%" data-sortable="false"><?php echo translate('options')?></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <!--===================================================-->
        <!-- End Striped Table -->
    </div>
    <!--===================================================-->
    <!--End page content-->
</div>

<script>
$(document).ready(function() {
    $('#membership_table').DataTable({
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
        "drawCallback": function(settings) {
            $('.add-tooltip').tooltip();
        }
    });
    
    setTimeout(function() {
        $('#success_alert').fadeOut('fast');
    }, 5000);
});
</script>
