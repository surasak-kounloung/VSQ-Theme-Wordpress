<?php
/**
 * The template for displaying single doctor posts.
 * Template Name: Doctor Single
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<div id="primary" class="content-area grid-container grid-parent">
	<main id="main" class="site-main">

		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				// Get Custom Fields
				$post_id = get_the_ID();
				
				// 1. Basic Info
				$doctor_image_id = get_post_meta( $post_id, '_doctor_image', true );
				$doctor_image_url = $doctor_image_id ? wp_get_attachment_image_url( $doctor_image_id, 'full' ) : '';
				
				$nickname = get_post_meta( $post_id, '_doctor_nickname', true );
				$fullname_th = get_post_meta( $post_id, '_doctor_fullname_th', true );
				$fullname_en = get_post_meta( $post_id, '_doctor_fullname_en', true );
				$medical_license_no = get_post_meta( $post_id, '_doctor_medical_license_no', true );
				
				// 2. Details
				$specialty = get_post_meta( $post_id, '_doctor_specialty', true );
				$education = get_post_meta( $post_id, '_doctor_education', true );
				$experience = get_post_meta( $post_id, '_doctor_experience', true );
				$certificates_text = get_post_meta( $post_id, '_doctor_certificates', true ); // Text/HTML content
				
				// 3. Galleries
				$certificate_gallery = get_post_meta( $post_id, '_doctor_certificate_gallery', true );
				$training_gallery = get_post_meta( $post_id, '_doctor_training_gallery', true );

				// 4. Schedule
				$schedule = get_post_meta( $post_id, '_doctor_schedule', true );

				// 5. Case Reviews
				$case_reviews = get_post_meta( $post_id, '_doctor_case_reviews', true );
				?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'doctor-single-article' ); ?>>
					
					<div class="doctor-container">
						<!-- Doctor Header: Image & Basic Info -->
						<div class="doctor-header">
							<div class="doctor-profile-image">
								<?php if ( $doctor_image_url ) : ?>
									<img src="<?php echo esc_url( $doctor_image_url ); ?>" alt="<?php the_title_attribute(); ?>">
								<?php elseif ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'full' ); ?>
								<?php else : ?>
									<!-- Placeholder if no image -->
									<div class="no-image-placeholder">No Image</div>
								<?php endif; ?>
							</div>
							
							<div class="doctor-profile-info">
								<!-- Name -->
								<h1 class="doctor-name">
									<?php 
									if ( $fullname_th ) {
										echo esc_html( $fullname_th );
									} else {
										the_title(); 
									}
									?>
								</h1>
								
								<?php if ( $fullname_en ) : ?>
									<div class="doctor-name-en"><?php echo esc_html( $fullname_en ); ?></div>
								<?php endif; ?>

								<?php if ( $nickname ) : ?>
									<div class="doctor-nickname"><strong>Nickname:</strong> <?php echo esc_html( $nickname ); ?></div>
								<?php endif; ?>

								<?php if ( $medical_license_no ) : ?>
									<div class="doctor-license"><strong>เลขที่ใบอนุญาต:</strong> <?php echo esc_html( $medical_license_no ); ?></div>
								<?php endif; ?>
								
								<!-- Specialty (HTML) -->
								<?php if ( ! empty( $specialty ) ) : ?>
									<div class="doctor-specialty-box">
										<h3 class="info-title">Specialty / Position</h3>
										<div class="info-content"><?php echo wp_kses_post( $specialty ); ?></div>
									</div>
								<?php endif; ?>
								
								<div class="doctor-content">
									<?php the_content(); ?>
								</div>

							</div>
						</div>

						<!-- Doctor Details: Sections -->
						<div class="doctor-details-wrapper">
							
							<!-- Education -->
							<?php if ( ! empty( $education ) ) : ?>
							<div class="doctor-section">
								<h3 class="doctor-section-title">Education History</h3>
								<div class="doctor-section-content">
									<?php echo wp_kses_post( $education ); ?>
								</div>
							</div>
							<?php endif; ?>

							<!-- Experience -->
							<?php if ( ! empty( $experience ) ) : ?>
							<div class="doctor-section">
								<h3 class="doctor-section-title">Work Experience</h3>
								<div class="doctor-section-content">
									<?php echo wp_kses_post( $experience ); ?>
								</div>
							</div>
							<?php endif; ?>

							<!-- Certificates (Text/HTML) -->
							<?php if ( ! empty( $certificates_text ) ) : ?>
							<div class="doctor-section">
								<h3 class="doctor-section-title">Certificates</h3>
								<div class="doctor-section-content">
									<?php echo wp_kses_post( $certificates_text ); ?>
								</div>
							</div>
							<?php endif; ?>

							<!-- Certificates Gallery -->
							<?php if ( ! empty( $certificate_gallery ) ) : 
								$cert_ids = explode( ',', $certificate_gallery );
							?>
							<div class="doctor-section doctor-gallery-section">
								<h3 class="doctor-section-title">Certificate Gallery</h3>
								<div class="doctor-gallery-grid">
									<?php foreach ( $cert_ids as $img_id ) : 
										$img_url = wp_get_attachment_image_url( $img_id, 'medium' );
										$img_full = wp_get_attachment_image_url( $img_id, 'full' );
										if ( $img_url ) :
									?>
										<div class="gallery-item">
											<a href="<?php echo esc_url( $img_full ); ?>" data-fancybox="cert-gallery">
												<img src="<?php echo esc_url( $img_url ); ?>" alt="Certificate">
											</a>
										</div>
									<?php endif; endforeach; ?>
								</div>
							</div>
							<?php endif; ?>

							<!-- Training Gallery -->
							<?php if ( ! empty( $training_gallery ) ) : 
								$train_ids = explode( ',', $training_gallery );
							?>
							<div class="doctor-section doctor-gallery-section">
								<h3 class="doctor-section-title">Training Gallery</h3>
								<div class="doctor-gallery-grid">
									<?php foreach ( $train_ids as $img_id ) : 
										$img_url = wp_get_attachment_image_url( $img_id, 'medium' );
										$img_full = wp_get_attachment_image_url( $img_id, 'full' );
										if ( $img_url ) :
									?>
										<div class="gallery-item">
											<a href="<?php echo esc_url( $img_full ); ?>" data-fancybox="training-gallery">
												<img src="<?php echo esc_url( $img_url ); ?>" alt="Training">
											</a>
										</div>
									<?php endif; endforeach; ?>
								</div>
							</div>
							<?php endif; ?>

							<!-- Doctor Schedule Table -->
							<?php if ( ! empty( $schedule ) && is_array( $schedule ) ) : ?>
								<div class="doctor-schedule-box">
									<h3 class="info-title">ตารางแพทย์</h3>
									<table class="doctor-schedule-table">
										<thead>
											<tr>
												<th>วัน</th>
												<th>สาขา</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ( $schedule as $row ) : 
												if ( empty( $row['date'] ) && empty( $row['branch'] ) ) continue;
											?>
											<tr>
												<td><?php echo esc_html( $row['date'] ); ?></td>
												<td><?php echo esc_html( $row['branch'] ); ?></td>
											</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>

							<div class="schedule-update-date">
								ข้อมูลอัปเดตล่าสุด: <?php echo do_shortcode( '[doctors_table_update_date]' ); ?>
							</div>

							<!-- Case Reviews -->
							<?php if ( ! empty( $case_reviews ) ) : 
								$review_ids = explode( ',', $case_reviews );
							?>
							<div class="doctor-section doctor-case-review-section">
								<h3 class="doctor-section-title">Case Reviews</h3>
								<div class="doctor-case-review-grid">
									<?php foreach ( $review_ids as $review_id ) : 
										$review_title = get_the_title( $review_id );
										$review_link = get_permalink( $review_id );
										$review_thumb_id = get_post_meta( $review_id, '_case_review_thumbnail', true );
										$review_thumb_url = $review_thumb_id ? wp_get_attachment_image_url( $review_thumb_id, 'medium_large' ) : '';
										
										// If no custom thumbnail, try featured image
										if ( ! $review_thumb_url && has_post_thumbnail( $review_id ) ) {
											$review_thumb_url = get_the_post_thumbnail_url( $review_id, 'medium_large' );
										}
									?>
										<div class="case-review-item">
											<a href="<?php echo esc_url( $review_link ); ?>" class="case-review-link">
												<div class="case-review-thumbnail">
													<?php if ( $review_thumb_url ) : ?>
														<img src="<?php echo esc_url( $review_thumb_url ); ?>" alt="<?php echo esc_attr( $review_title ); ?>">
													<?php else : ?>
														<div class="no-image-placeholder small">No Image</div>
													<?php endif; ?>
												</div>
												<h4 class="case-review-title"><?php echo esc_html( $review_title ); ?></h4>
											</a>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>

						</div>
					</div>

				</article>

			<?php
			endwhile;
		else :
			echo '<p>No doctor found.</p>';
		endif;
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<style>
/* Doctor Single Page Styles */
.doctor-container {
	background: #fff;
	padding: 40px;
	border-radius: 8px;
	box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

.doctor-header {
	display: flex;
	gap: 40px;
	margin-bottom: 60px;
	align-items: flex-start;
}

.doctor-profile-image {
	flex: 0 0 350px;
	width: 350px;
	border-radius: 10px;
	overflow: hidden;
	box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.doctor-profile-image img {
	width: 100%;
	height: auto;
	display: block;
}

.no-image-placeholder {
	width: 100%;
	height: 300px;
	background: #f0f0f0;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #999;
	font-size: 1.2em;
}

.no-image-placeholder.small {
	height: 200px;
	font-size: 1em;
}

.doctor-profile-info {
	flex: 1;
}

.doctor-name {
	font-size: 2.2em;
	margin-bottom: 5px;
	color: #333;
	line-height: 1.2;
}

.doctor-name-en {
	font-size: 1.4em;
	color: #666;
	margin-bottom: 15px;
	font-weight: 500;
}

.doctor-nickname, .doctor-license {
	font-size: 1.1em;
	color: #555;
	margin-bottom: 8px;
}

.doctor-specialty-box {
	margin-top: 25px;
	padding: 20px;
	background: #f9f9f9;
	border-left: 4px solid #d4af37;
	border-radius: 0 4px 4px 0;
}

.info-title {
	font-size: 1.1em;
	font-weight: 700;
	color: #d4af37;
	text-transform: uppercase;
	margin-bottom: 10px;
	letter-spacing: 1px;
}

.info-content {
	font-size: 1.1em;
	line-height: 1.6;
}

/* Schedule Table */
.doctor-schedule-box {
	margin-top: 30px;
}

.doctor-schedule-table {
	width: 100%;
	border-collapse: collapse;
	background: #fff;
	border: 1px solid #eee;
	box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.doctor-schedule-table th,
.doctor-schedule-table td {
	padding: 12px 15px;
	border-bottom: 1px solid #eee;
	text-align: left;
	font-size: 1em;
}

.doctor-schedule-table th {
	background: #f4f4f4;
	font-weight: 600;
	color: #333;
	text-transform: uppercase;
	font-size: 0.9em;
	letter-spacing: 0.5px;
}

.doctor-schedule-table tr:last-child td {
	border-bottom: none;
}

.doctor-schedule-table tr:hover td {
	background-color: #fafafa;
}

.schedule-update-date {
	margin-top: 10px;
	font-size: 0.9em;
	color: #777;
	text-align: right;
	font-style: italic;
}

/* Sections */
.doctor-section {
	margin-bottom: 40px;
	border-top: 1px solid #eee;
	padding-top: 30px;
}

.doctor-section-title {
	font-size: 1.6em;
	margin-bottom: 25px;
	color: #444;
	position: relative;
	padding-left: 15px;
	border-left: 4px solid #d4af37;
}

.doctor-section-content {
	font-size: 1.1em;
	line-height: 1.7;
	color: #333;
}

/* Gallery Grid */
.doctor-gallery-grid {
	display: flex;
	flex-wrap: wrap;
	gap: 15px;
}

.gallery-item {
	width: 160px;
	height: 160px;
	border: 1px solid #eee;
	border-radius: 6px;
	overflow: hidden;
	transition: all 0.3s ease;
}

.gallery-item:hover {
	transform: translateY(-5px);
	box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.gallery-item img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}

/* Case Review Grid */
.doctor-case-review-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
	gap: 20px;
}

.case-review-item {
	border: 1px solid #eee;
	border-radius: 8px;
	overflow: hidden;
	transition: all 0.3s ease;
	background: #fff;
}

.case-review-item:hover {
	transform: translateY(-5px);
	box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.case-review-link {
	text-decoration: none;
	color: inherit;
	display: block;
}

.case-review-thumbnail {
	position: relative;
	width: 100%;
	padding-top: 56.25%; /* 16:9 Aspect Ratio */
	background: #f9f9f9;
	overflow: hidden;
}

.case-review-thumbnail img {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	object-fit: cover;
	transition: transform 0.5s ease;
}

.case-review-item:hover .case-review-thumbnail img {
	transform: scale(1.05);
}

.case-review-title {
	padding: 15px;
	margin: 0;
	font-size: 1.1em;
	line-height: 1.4;
	color: #333;
	font-weight: 600;
}

.case-review-item:hover .case-review-title {
	color: #d4af37;
}

/* Responsive */
@media (max-width: 900px) {
	.doctor-header {
		flex-direction: column;
	}
	
	.doctor-profile-image {
		flex: none;
		width: 100%;
		max-width: 400px;
		margin: 0 auto 30px;
	}
	
	.doctor-profile-info {
		text-align: center;
		width: 100%;
	}

	.doctor-specialty-box,
	.doctor-schedule-box {
		text-align: left;
	}
	
	.doctor-gallery-grid {
		justify-content: center;
	}
}
</style>

<?php
get_footer();
