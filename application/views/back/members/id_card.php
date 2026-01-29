<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Membership Card</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            flex-direction: column;
            gap: 30px;
        }

        .download-btn-container {
            margin-bottom: 20px;
            text-align: center;
        }

        .download-btn {
            background: #1e3c72;
            color: #fff;
            padding: 12px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .download-btn:hover {
            background: #2a5298;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .cards-container {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .id-card {
            width: 325px; /* Standard ID card ratio-ish, typically 85.6mm x 53.98mm but scaled up */
            height: 520px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* --- FRONT SIDE --- */
        .card-front .header-curve {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-bottom-left-radius: 50% 30px;
            border-bottom-right-radius: 50% 30px;
            z-index: 1;
            display: flex;
            justify-content: center;
            padding-top: 25px;
        }

        .logo-img {
            height: 45px;
            background: #fff;
            padding: 4px 8px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .photo-wrapper {
            position: relative;
            margin-top: 90px;
            z-index: 2;
            display: flex;
            justify-content: center;
        }

        .photo-border {
            width: 130px;
            height: 130px;
            background: #fff;
            border-radius: 50%;
            padding: 5px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .photo {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #1e3c72;
        }

        .member-info {
            text-align: center;
            margin-top: 15px;
            padding: 0 20px;
        }

        .member-name {
            font-size: 20px;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .member-role {
            font-size: 14px;
            color: #00a8cc;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .info-grid {
            padding: 0 25px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 6px 0;
            border-bottom: 1px solid #ebedf0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #8898aa;
            font-weight: 500;
        }

        .value {
            color: #32325d;
            font-weight: 600;
            text-align: right;
        }

        .qr-section {
            margin-top: auto;
            background: #f8fafc;
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-top: 1px solid #ebedf0;
        }
        
        .qr-placeholder {
            width: 50px;
            height: 50px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%231e3c72"><path d="M10,10 h30 v30 h-30 z M60,10 h30 v30 h-30 z M10,60 h30 v30 h-30 z M50,50 h10 v10 h-10 z M70,50 h10 v10 h-10 z M50,70 h10 v10 h-10 z M70,70 h10 v10 h-10 z M20,20 v10 h10 v-10 z M70,20 v10 h10 v-10 z M20,70 v10 h10 v-10 z"/></svg>');
            background-size: cover;
            opacity: 0.8;
        }

        /* --- BACK SIDE --- */
        .card-back {
            background: white;
            position: relative;
        }

        .back-header {
            height: 80px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            align-items: center;
            padding: 0 20px;
            color: white;
        }

        .back-title {
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .back-body {
            padding: 25px;
            font-size: 13px;
            color: #525f7f;
            flex: 1;
        }

        .key-dates {
            display: flex;
            justify-content: space-between;
            background: #f6f9fc;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .date-box {
            display: flex;
            flex-direction: column;
        }

        .date-label {
            font-size: 11px;
            color: #8898aa;
            text-transform: uppercase;
        }

        .date-val {
            font-size: 14px;
            font-weight: 600;
            color: #1e3c72;
        }

        .contact-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            gap: 10px;
        }

        .icon-circle {
            width: 24px;
            height: 24px;
            background: #eef2f7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e3c72;
            font-size: 12px;
        }

        .terms {
            margin-top: 20px;
            font-size: 11px;
            line-height: 1.5;
            color: #8898aa;
            border-top: 1px solid #ebedf0;
            padding-top: 15px;
        }

        .signature-area {
            margin-top: 30px;
            text-align: center;
        }
        
        .sig-img {
            height: 30px;
            margin-bottom: 5px;
            opacity: 0.7;
        }

        .sig-line {
            width: 150px;
            height: 1px;
            background: #cbd5e0;
            margin: 0 auto 5px;
        }

        .sig-text {
            font-size: 11px;
            color: #8898aa;
            font-weight: 500;
        }

        .website-footer {
            margin-top: auto;
            text-align: center;
            padding: 15px;
            background: #1e3c72;
            color: rgba(255,255,255,0.8);
            font-size: 12px;
            letter-spacing: 1px;
        }

        @media print {
            body { 
                background: white; 
                -webkit-print-color-adjust: exact; 
                padding: 0;
            }
            .download-btn-container { display: none; }
            .cards-container { gap: 0; }
            .id-card { 
                margin: 0; 
                box-shadow: none; 
                border: 1px solid #eee;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<?php 
    // Data Setup
    $value = $get_premium_member_by_id[0];
    
    // Decoding JSON data
    $image = json_decode($value->profile_image, true);
    $basic_info = json_decode($value->basic_info, true);
    
    // Legion Lookup
    $legion_name = "N/A";
    if(!empty($value->legion_id)) {
        $legion_query = $this->db->get_where('legions', array('id' => $value->legion_id));
        if($legion_query->num_rows() > 0){
            $legion_name = $legion_query->row()->name;
        }
    }
    
    // Helper Variables
    $area_name = !empty($basic_info[0]['area']) ? ucfirst($basic_info[0]['area']) : "N/A";
    $full_name = $value->first_name . " " . $value->last_name;
    
    // Profile Image Logic
    $profile_pic = base_url().'uploads/profile_image/default.jpg';
    if(isset($image[0]['profile_image']) && file_exists('uploads/profile_image/'.$image[0]['profile_image'])){
        $profile_pic = base_url().'uploads/profile_image/'.$image[0]['profile_image'];
    } elseif(isset($image[0]['thumb']) && file_exists('uploads/profile_image/'.$image[0]['thumb'])) {
         $profile_pic = base_url().'uploads/profile_image/'.$image[0]['thumb'];
    }
?>

    <?php if(!isset($download_pdf)): ?>
    <div class="download-btn-container">
        <a href="<?=base_url()?>admin/members/<?=$parameter?>/download_id_card/<?=$value->member_id?>" class="download-btn">
            <i class="fa fa-download"></i> Download PDF
        </a>
    </div>
    <?php endif; ?>

    <div class="cards-container">
        
        <!-- FRONT SIDE -->
        <div class="id-card card-front">
            <div class="header-curve">
                <img src="<?=base_url()?>uploads/logo1.jpg" alt="Logo" class="logo-img">
            </div>
            
            <div class="photo-wrapper">
                <div class="photo-border">
                    <img src="<?=$profile_pic?>" alt="<?=$full_name?>" class="photo">
                </div>
            </div>

            <div class="member-info">
                <h1 class="member-name"><?=$full_name?></h1>
                <div class="member-role">Member</div>
            </div>

            <div class="info-grid">
                <div class="info-row">
                    <span class="label">Member ID</span>
                    <span class="value"><?=$value->member_profile_id?></span>
                </div>
                <div class="info-row">
                    <span class="label">Date of Birth</span>
                    <span class="value"><?=date('d-m-Y', $value->date_of_birth)?></span>
                </div>
                <div class="info-row">
                    <span class="label">Phone</span>
                    <span class="value"><?=$value->mobile?></span>
                </div>
                <div class="info-row">
                    <span class="label">Area</span>
                    <span class="value"><?=$area_name?></span>
                </div>
                <div class="info-row">
                    <span class="label">Legion</span>
                    <span class="value"><?=$legion_name?></span>
                </div>
                <div class="info-row" style="border-bottom: none;">
                    <span class="label">Email</span>
                    <span class="value" style="font-size: 11px;"><?=$value->email?></span>
                </div>
            </div>

            <div class="qr-section">
                <!-- Simple CSS QR Placeholder -->
                <div class="qr-placeholder"></div>
            </div>
        </div>

        <!-- BACK SIDE -->
        <div class="id-card card-back">
            <div class="back-header">
                <div class="back-title">Member Details</div>
            </div>
            
            <div class="back-body">
                <div class="key-dates">
                    <div class="date-box">
                        <span class="date-label">Joined On</span>
                        <span class="date-val"><?=date('d M Y', $value->member_since)?></span>
                    </div>
                    <div class="date-box" style="text-align: right;">
                        <span class="date-label">Valid Until</span>
                        <span class="date-val">Lifetime</span>
                    </div>
                </div>

                <div class="contact-row">
                    <div class="icon-circle">📞</div>
                    <div>
                        <div class="label" style="font-size: 10px;">Emergency Contact</div>
                        <div class="value" style="text-align: left; font-size: 12px;">+91 98765 43210</div>
                    </div>
                </div>
                
                <div class="contact-row">
                    <div class="icon-circle">🌐</div>
                    <div>
                        <div class="label" style="font-size: 10px;">Website</div>
                        <div class="value" style="text-align: left; font-size: 12px;">www.seniorchamber.com</div>
                    </div>
                </div>

                <div class="terms">
                    <strong>Terms & Conditions:</strong><br>
                    1. This card remains the property of Senior Chamber International.<br>
                    2. If found, please return to the nearest organization office.<br>
                    3. Misuse of this card allows the organization to revoke membership.
                </div>

                <div class="signature-area">
                    <!-- Signature Font Placeholder -->
                    <div style="font-family: 'Brush Script MT', cursive; font-size: 24px; color: #1e3c72; margin-bottom: 5px;">Authorized</div>
                    <div class="sig-line"></div>
                    <div class="sig-text">Authorized Signature</div>
                </div>
            </div>

            <div class="website-footer">
                SENIOR CHAMBER INTERNATIONAL
            </div>
        </div>

    </div>

</body>
</html>
