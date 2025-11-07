<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
    <div id="page-head">
        <!--Page Title-->
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <div id="page-title">
            <h1 class="page-header text-overflow"><?php echo translate('edit_membership_type')?></h1>
        </div>
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <!--End page title-->

        <!--Breadcrumb-->
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><?php echo translate('home')?></a></li>
            <li><a href="<?=base_url()?>admin/membership_management"><?php echo translate('membership_types')?></a></li>
            <li class="active"><?php echo translate('edit')?></li>
        </ol>
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <!--End breadcrumb-->
    </div>

    <!--Page content-->
    <!--===================================================-->
    <div id="page-content">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo translate('edit_membership_type')?></h3>
            </div>
            <div class="panel-body">
                <form method="post" action="<?=base_url()?>admin/membership_management/update/<?=$membership->id?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo translate('membership_name')?> <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?=$membership->name?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo translate('slug')?> <span class="text-danger">*</span></label>
                                <input type="text" name="slug" class="form-control" value="<?=$membership->slug?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo translate('membership_value')?> <span class="text-danger">*</span></label>
                                <input type="number" name="membership_value" class="form-control" value="<?=$membership->membership_value?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo translate('display_order')?></label>
                                <input type="number" name="display_order" class="form-control" value="<?=$membership->display_order?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo translate('status')?></label>
                                <select name="status" class="form-control">
                                    <option value="active" <?=$membership->status=='active'?'selected':''?>><?php echo translate('active')?></option>
                                    <option value="inactive" <?=$membership->status=='inactive'?'selected':''?>><?php echo translate('inactive')?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> <?php echo translate('update')?>
                        </button>
                        <a href="<?=base_url()?>admin/membership_management" class="btn btn-default">
                            <?php echo translate('cancel')?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--===================================================-->
    <!--End page content-->
</div>
