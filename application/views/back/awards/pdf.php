<!DOCTYPE html>
<html>
<head>
    <title>Award Details</title>
    <style>
        @page {
            margin: 10mm;
            border: 3px double #000; /* Double border for all pages */
            padding: 10mm;
        }
        body { 
            font-family: 'Times New Roman', serif; 
            font-size: 14pt; 
            color: #000; 
            margin: 0; padding: 0; 
        }
        
        /* Typography */
        h1.org-title {
            font-family: 'Times New Roman', serif; 
            font-size: 28pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        h2.sub-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 30px;
            text-transform: uppercase;
            line-height: 1.4;
        }

        /* Cover Page Fields */
        .cover-field {
            margin-bottom: 15px;
            font-size: 14pt;
        }
        .label {
            color: red;
            font-weight: bold;
            display: inline-block;
            width: 250px; /* Fixed width for alignment */
        }
        .value {
            color: #000;
            font-weight: bold;
        }

        /* Footer Signature */
        .signature-section {
            position: absolute;
            bottom: 50px;
            right: 50px;
            text-align: right;
            width: 300px;
        }

        /* Content Pages */
        .content-header {
            color: red;
            font-weight: bold;
            text-align: center;
            font-size: 14pt;
            margin-top: 20px;
            margin-bottom: 15px;
            text-transform: capitalize;
        }
        .content-text {
            text-align: justify;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        /* Image Grid */
        .img-grid {
            width: 100%;
            margin-top: 10px;
        }
        .img-cell {
            width: 48%; /* 2 columns */
            padding: 5px;
            text-align: center;
            vertical-align: top;
        }
        .evidence-img {
            max-width: 100%; 
            height: auto; 
            max-height: 200px;
            border: 1px solid #ccc;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .logo-center {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <!-- Cover Page -->
    <div class="logo-center">
        <!-- Using the most likely logo found -->
        <img src="<?= FCPATH . 'template/front/images/logo.png'; ?>" width="120">
    </div>

    <h1 class="org-title">Senior Chamber International</h1>
    <h2 class="sub-title">
        NATIONAL AWARDS<br>
        AWARD ENTRY FORM <?= $entry['year']; ?> - <?= $entry['year'] + 1; ?><br>
        <span style="font-size: 12pt; font-weight: normal; text-transform: none;">Awards for Legions / Awards for Individuals</span>
    </h2>

    <div style="margin-left: 20px;">
        <div class="cover-field">
            <span class="label">Name of the Award:</span>
            <span class="value"><?= $entry['category']; ?></span>
        </div>
        
        <div class="cover-field">
            <span class="label">Name of the Legion:</span>
            <span class="value"><?= $entry['legion_name']; ?></span>
        </div>

        <?php if($entry['award_for'] == 'individual'): ?>
            <div class="cover-field">
                <span class="label">Name of the President:</span>
                <span class="value"><?= isset($entry['president_name']) ? $entry['president_name'] : 'N/A'; ?></span>
            </div>
            
            <div class="cover-field">
                <span class="label">Name of the Nominee:</span>
                <span class="value"><?= $entry['nominee_name']; ?></span>
            </div>
        <?php endif; ?>

        <?php 
        // Extract extra fields from form_data if available
        $form_data = ($entry['award_for'] == 'legion') 
            ? json_decode($entry['legion_form_json'], true) 
            : json_decode($entry['individual_form_json'], true);
        ?>

        <div class="cover-field">
            <span class="label">Address of the Legion:</span>
            <span class="value"><?= isset($form_data['legion_address']) ? $form_data['legion_address'] : 'N/A'; ?></span>
        </div>

        <div class="cover-field">
            <span class="label">Number of Members:</span>
            <span class="value"><?= isset($form_data['members_count']) ? $form_data['members_count'] : 'N/A'; ?></span>
        </div>

        <div class="cover-field">
            <span class="label">Date of Affiliation:</span>
            <span class="value"><?= isset($form_data['affiliation_date']) ? $form_data['affiliation_date'] : 'N/A'; ?></span>
        </div>

         <div class="cover-field">
            <span class="label">Date:</span>
            <span class="value"><?= date('d/m/Y', strtotime($entry['created_at'])); ?></span>
        </div>
    </div>

    <div class="signature-section">
        <p>Signature</p>
        <p style="margin-top: 40px;">President/Secretary</p>
    </div>

    <!-- Page Break for Content -->
    <div class="page-break"></div>

    <!-- Content Pages -->
    <?php if (isset($form_data['criteria_data']) && is_array($form_data['criteria_data'])): ?>
        <?php foreach ($form_data['criteria_data'] as $crit): ?>
            
            <div class="content-header"><?= $crit['name']; ?></div>
            
            <div class="content-text">
                <?= nl2br($crit['description']); ?>
            </div>

            <?php if (!empty($crit['images'])): ?>
                <table class="img-grid">
                    <tr>
                        <?php 
                        $count = 0;
                        foreach ($crit['images'] as $img): 
                            if ($count > 0 && $count % 2 == 0) {
                                echo '</tr><tr>'; // New row every 2 images
                            }
                        ?>
                            <td class="img-cell">
                                <img src="<?= FCPATH . $img; ?>" class="evidence-img">
                            </td>
                        <?php 
                            $count++;
                        endforeach; 
                        
                        // Fill empty cell if odd number of images
                        if ($count % 2 != 0) {
                            echo '<td class="img-cell"></td>';
                        }
                        ?>
                    </tr>
                </table>
            <?php endif; ?>

            <div style="margin-bottom: 30px;"></div> <!-- Spacer between criteria -->
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
