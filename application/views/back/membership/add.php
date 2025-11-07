<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">Add Membership Type</h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="demo-pli-home"></i></a></li>
            <li><a href="<?=base_url()?>admin/membership_management">Membership Types</a></li>
            <li class="active">Add</li>
        </ol>
    </div>

    <div id="page-content">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Add New Membership Type</h3>
            </div>
            <form method="post" action="<?=base_url()?>admin/membership_management/do_add" class="form-horizontal">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Membership Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Slug <span class="text-danger">*</span></label>
                                <input type="text" name="slug" class="form-control" placeholder="e.g., gold_members" required>
                                <small class="help-block">URL friendly name (use underscore)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Membership Value <span class="text-danger">*</span></label>
                                <input type="number" name="membership_value" class="form-control" required>
                                <small class="help-block">Unique number stored in database</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" name="display_order" class="form-control" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save
                    </button>
                    <a href="<?=base_url()?>admin/membership_management" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
