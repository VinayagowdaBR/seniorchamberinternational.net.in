<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow"><?php echo translate('Add_project')?></h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><?php echo translate('home')?></a></li>
            <li class="active"><?php echo translate('add_project')?></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-md-10 col-lg-offset-1">
            <div id="page-content">
                <div class="panel">
                    <form class="form-horizontal" id="project_form" method="POST" action="<?=base_url()?>admin/add_story_details" enctype="multipart/form-data">
                        <div class="panel-body" style="padding: 50px 20px;">
                            <!-- Display General Error (if any) -->
                            <?php if (isset($this->session->flashdata('failed')['general'])): ?>
                                <div class="alert alert-danger text-center" role="alert" style="margin-bottom: 20px;">
                                    <?= $this->session->flashdata('failed')['general']; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Legion Selection -->
                           

                            <!-- President Name -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><b>President Name</b><span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="president_name" name="president_name" value="<?= htmlspecialchars(isset($legion['admin_name']) ? $legion['admin_name'] : '') ?>" required readonly>
                                    <?php if (isset($this->session->flashdata('failed')['president_name'])): ?>
                                        <span class="text-danger"><?= $this->session->flashdata('failed')['president_name']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Area -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><b>Area</b><span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="area" name="area" value="<?= htmlspecialchars(isset($legion['area_name']) ? $legion['area_name'] : '') ?>" required readonly>
                                    <?php if (isset($this->session->flashdata('failed')['area'])): ?>
                                        <span class="text-danger"><?= $this->session->flashdata('failed')['area']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Legion Name -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><b>Legion Name</b><span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="legion_name" name="legion_name" value="<?= htmlspecialchars(isset($legion['legion_name']) ? $legion['legion_name'] : '') ?>" required readonly>
                                    <?php if (isset($this->session->flashdata('failed')['legion_name'])): ?>
                                        <span class="text-danger"><?= $this->session->flashdata('failed')['legion_name']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Date -->
                <div class="form-group">
                    <label class="col-sm-2 control-label"><b>Date</b><span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control" id="date" name="date" 
                            value="<?= date('Y-m-d') ?>" 
                            readonly 
                            style="pointer-events: none;" required>
                        <?php if (isset($this->session->flashdata('failed')['date'])): ?>
                            <span class="text-danger"><?= $this->session->flashdata('failed')['date']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Program Date -->
            <div class="form-group">
                <label class="col-sm-2 control-label"><b>Program Date</b><span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="date" class="form-control" id="program_date" name="program_date" required>
                    <?php if (isset($this->session->flashdata('failed')['program_date'])): ?>
                        <span class="text-danger"><?= $this->session->flashdata('failed')['program_date']; ?></span>
                    <?php endif; ?>
                </div>
            </div>


                            <!-- Program Name -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><b>Program Name</b><span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="program_name" name="program_name" required>
                                    <?php if (isset($this->session->flashdata('failed')['program_name'])): ?>
                                        <span class="text-danger"><?= $this->session->flashdata('failed')['program_name']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Program Area -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><b>Program Area</b><span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="program_area" name="program_area" required>
                                    <?php if (isset($this->session->flashdata('failed')['program_area'])): ?>
                                        <span class="text-danger"><?= $this->session->flashdata('failed')['program_area']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Program Details -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><b>Program Details</b><span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" id="program_details" name="program_details" rows="5" required></textarea>
                                    <?php if (isset($this->session->flashdata('failed')['program_details'])): ?>
                                        <span class="text-danger"><?= $this->session->flashdata('failed')['program_details']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Activity Photo -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><b>Activity Photo</b><span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="file" class="form-control" id="activity_photo" name="activity_photo" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <small>Size: 10KB - 5MB (JPG, JPEG, PNG, PDF)</small>
                                    <?php if (isset($this->session->flashdata('failed')['activity_photo'])): ?>
                                        <span class="text-danger"><?= $this->session->flashdata('failed')['activity_photo']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Press Coverage -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><b>Press Coverage</b></label>
                                <div class="col-sm-8">
                                    <input type="file" class="form-control" id="press_coverage" name="press_coverage" accept=".jpg,.jpeg,.png,.pdf">
                                    <small>Size: 10KB - 5MB (images), 50KB - 10MB (PDFs)</small>
                                    <?php if (isset($this->session->flashdata('failed')['press_coverage'])): ?>
                                        <span class="text-danger"><?= $this->session->flashdata('failed')['press_coverage']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="panel-footer text-center">
                            <button class="btn btn-primary btn-sm btn-labeled fa fa-floppy-o" type="submit">
                                <?php echo translate('submit')?>
                            </button>
                            <a href="<?=base_url()?>admin/stories" class="btn btn-danger btn-sm btn-labeled fa fa-step-backward">
                                <?php echo translate('go_back')?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillLegionDetails() {
    const select = document.getElementById('legion_id');
    const selectedOption = select.options[select.selectedIndex];
    document.getElementById('legion_name').value = selectedOption.getAttribute('data-legion-name') || '';
    document.getElementById('president_name').value = selectedOption.getAttribute('data-president-name') || '';
    document.getElementById('area').value = selectedOption.getAttribute('data-area') || '';
}

// Client-side file validation
document.getElementById('project_form').addEventListener('submit', function(e) {
    // Validate Activity Photo
    const activityPhoto = document.getElementById('activity_photo').files[0];
    const maxSizePhoto = 5 * 1024 * 1024; // 5MB
    const minSizePhoto = 10 * 1024; // 10KB
    const allowedTypesPhoto = ['image/jpeg', 'image/png', 'application/pdf'];

    if (activityPhoto) {
        if (activityPhoto.size > maxSizePhoto || activityPhoto.size < minSizePhoto) {
            e.preventDefault();
            alert('Activity photo must be between 10KB and 5MB.');
            return;
        }
        if (!allowedTypesPhoto.includes(activityPhoto.type)) {
            e.preventDefault();
            alert('Activity photo must be a JPG, PNG, or PDF.');
            return;
        }
    }

    // Validate Press Coverage (optional)
    const pressCoverage = document.getElementById('press_coverage').files[0];
    if (pressCoverage) {
        const maxSizeImage = 5 * 1024 * 1024; // 5MB for images
        const maxSizePDF = 10 * 1024 * 1024; // 10MB for PDFs
        const minSizeImage = 10 * 1024; // 10KB for images
        const minSizePDF = 50 * 1024; // 50KB for PDFs
        const allowedTypesPress = ['image/jpeg', 'image/png', 'application/pdf'];

        if (!allowedTypesPress.includes(pressCoverage.type)) {
            e.preventDefault();
            alert('Press coverage must be a JPG, PNG, or PDF.');
            return;
        }
        if (pressCoverage.type === 'application/pdf') {
            if (pressCoverage.size > maxSizePDF || pressCoverage.size < minSizePDF) {
                e.preventDefault();
                alert('Press coverage PDF must be between 50KB and 10MB.');
                return;
            }
        } else {
            if (pressCoverage.size > maxSizeImage || pressCoverage.size < minSizeImage) {
                e.preventDefault();
                alert('Press coverage image must be between 10KB and 5MB.');
                return;
            }
        }
    }

    // Validate Program Details length
    const programDetails = document.getElementById('program_details').value;
    if (programDetails.length < 10) {
        e.preventDefault();
        alert('Program details must be at least 10 characters long.');
        return;
    }
});

// Trigger fillLegionDetails on page load
window.onload = fillLegionDetails;
</script>