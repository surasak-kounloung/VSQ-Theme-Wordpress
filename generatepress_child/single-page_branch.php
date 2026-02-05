<?php
/**
 * The template for displaying single branch posts.
 * Template Name: Branch Single
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
				
				// 1. Images
				$branch_thumbnail_id = get_post_meta( $post_id, '_branch_thumbnail', true );
				$branch_thumbnail_url = $branch_thumbnail_id ? wp_get_attachment_image_url( $branch_thumbnail_id, 'full' ) : '';
				
				$branch_thumbnail_name_id = get_post_meta( $post_id, '_branch_thumbnail_name', true );
				$branch_thumbnail_name_url = $branch_thumbnail_name_id ? wp_get_attachment_image_url( $branch_thumbnail_name_id, 'full' ) : '';
				
				$branch_image_360_id = get_post_meta( $post_id, '_branch_image_360', true );
				$branch_image_360_url = $branch_image_360_id ? wp_get_attachment_image_url( $branch_image_360_id, 'full' ) : '';

				// 2. Info
				$branch_title = get_post_meta( $post_id, '_branch_title', true );
				$branch_title_floor = get_post_meta( $post_id, '_branch_title_floor', true );
				$branch_address = get_post_meta( $post_id, '_branch_address', true );
				
				// 3. Contact
				$branch_telephone = get_post_meta( $post_id, '_branch_telephone', true );
				$branch_id_line = get_post_meta( $post_id, '_branch_id_line', true );
				$branch_url_line = get_post_meta( $post_id, '_branch_url_line', true );
				$branch_google_map = get_post_meta( $post_id, '_branch_google_map', true );

				// 4. Transportation
				$branch_car = get_post_meta( $post_id, '_branch_car', true );
				$branch_bts_or_mrt = get_post_meta( $post_id, '_branch_bts_or_mrt', true );
				$branch_bus = get_post_meta( $post_id, '_branch_bus', true );

				// 5. Opening Time
				$branch_opening_time = get_post_meta( $post_id, '_branch_opening_time', true );
				?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'branch-single-article' ); ?>>
					
					<div class="branch-container">
						
						<!-- Branch Header -->
						<div class="branch-header">
							<h1 class="branch-title">
								<?php 
								if ( $branch_title ) {
									echo esc_html( $branch_title );
								} else {
									the_title(); 
								}
								?>
							</h1>
							<?php if ( $branch_title_floor ) : ?>
								<div class="branch-floor"><?php echo esc_html( $branch_title_floor ); ?></div>
							<?php endif; ?>
							
							<?php if ( $branch_address ) : ?>
								<div class="branch-address">
									<span class="dashicons dashicons-location"></span> 
									<?php echo esc_html( $branch_address ); ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="branch-content-wrapper">
							
							<!-- Left Column: Images & Map -->
							<div class="branch-left-col">
								<?php if ( $branch_thumbnail_url ) : ?>
									<div class="branch-main-image">
										<img src="<?php echo esc_url( $branch_thumbnail_url ); ?>" alt="<?php the_title_attribute(); ?>">
									</div>
								<?php endif; ?>

								<?php if ( $branch_thumbnail_name_url ) : ?>
									<div class="branch-secondary-image">
										<img src="<?php echo esc_url( $branch_thumbnail_name_url ); ?>" alt="Branch Name Image">
									</div>
								<?php endif; ?>

								<?php if ( $branch_image_360_url ) : ?>
									<div class="branch-360-image">
										<h3 class="branch-section-title">ภาพบรรยากาศ 360 องศา</h3>
										<img src="<?php echo esc_url( $branch_image_360_url ); ?>" alt="360 View">
									</div>
								<?php endif; ?>

								<?php if ( $branch_google_map ) : ?>
									<div class="branch-map-button">
										<a href="<?php echo esc_url( $branch_google_map ); ?>" target="_blank" class="button button-primary">
											<span class="dashicons dashicons-google"></span> ดูแผนที่ Google Map
										</a>
									</div>
								<?php endif; ?>
							</div>

							<!-- Right Column: Details, Contact, Hours -->
							<div class="branch-right-col">
								
								<!-- Contact Info -->
								<div class="branch-section branch-contact-box">
									<h3 class="branch-section-title">ข้อมูลติดต่อ</h3>
									
									<?php if ( $branch_telephone ) : ?>
										<div class="contact-row">
											<strong>โทรศัพท์:</strong> 
											<a href="tel:<?php echo esc_attr( $branch_telephone ); ?>"><?php echo esc_html( $branch_telephone ); ?></a>
										</div>
									<?php endif; ?>

									<?php if ( $branch_id_line ) : ?>
										<div class="contact-row">
											<strong>LINE ID:</strong> 
											<?php if ( $branch_url_line ) : ?>
												<a href="<?php echo esc_url( $branch_url_line ); ?>" target="_blank"><?php echo esc_html( $branch_id_line ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $branch_id_line ); ?>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>

								<!-- Opening Hours -->
								<?php if ( ! empty( $branch_opening_time ) && is_array( $branch_opening_time ) ) : ?>
									<div class="branch-section branch-hours-box">
										<h3 class="branch-section-title">เวลาทำการ</h3>
										<table class="branch-hours-table">
											<thead>
												<tr>
													<th>วัน</th>
													<th>เวลา</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ( $branch_opening_time as $row ) : 
													if ( empty( $row['day'] ) && empty( $row['time'] ) ) continue;
												?>
												<tr>
													<td><?php echo esc_html( $row['day'] ); ?></td>
													<td><?php echo esc_html( $row['time'] ); ?></td>
												</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php endif; ?>

								<!-- Transportation -->
								<div class="branch-section branch-transport-box">
									<h3 class="branch-section-title">การเดินทาง</h3>
									
									<?php if ( $branch_car ) : ?>
										<div class="transport-item">
											<div class="transport-icon"><span class="dashicons dashicons-car"></span> รถยนต์ส่วนตัว</div>
											<div class="transport-detail"><?php echo esc_html( $branch_car ); ?></div>
										</div>
									<?php endif; ?>

									<?php if ( $branch_bts_or_mrt ) : ?>
										<div class="transport-item">
											<div class="transport-icon"><span class="dashicons dashicons-location-alt"></span> BTS / MRT</div>
											<div class="transport-detail"><?php echo esc_html( $branch_bts_or_mrt ); ?></div>
										</div>
									<?php endif; ?>

									<?php if ( $branch_bus ) : ?>
										<div class="transport-item">
											<div class="transport-icon"><span class="dashicons dashicons-bus-side"></span> รถประจำทาง</div>
											<div class="transport-detail"><?php echo nl2br( esc_html( $branch_bus ) ); ?></div>
										</div>
									<?php endif; ?>
								</div>

							</div>
						</div>
					</div>

				</article>

			<?php
			endwhile;
		else :
			echo '<p>Branch not found.</p>';
		endif;
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<style>
/* Branch Single Page Styles */
.branch-container {
	background: #fff;
	padding: 40px;
	border-radius: 8px;
	box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

.branch-header {
	margin-bottom: 40px;
	padding-bottom: 20px;
	border-bottom: 1px solid #eee;
}

.branch-title {
	font-size: 2.5em;
	margin-bottom: 10px;
	color: #333;
}

.branch-floor {
	font-size: 1.2em;
	color: #666;
	margin-bottom: 15px;
}

.branch-address {
	font-size: 1.1em;
	color: #555;
	display: flex;
	align-items: center;
	gap: 5px;
}

.branch-content-wrapper {
	display: flex;
	gap: 40px;
}

.branch-left-col {
	flex: 0 0 45%;
	max-width: 45%;
}

.branch-right-col {
	flex: 1;
}

/* Images */
.branch-main-image {
	margin-bottom: 20px;
	border-radius: 8px;
	overflow: hidden;
	box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.branch-main-image img,
.branch-secondary-image img,
.branch-360-image img {
	width: 100%;
	height: auto;
	display: block;
}

.branch-secondary-image {
	margin-bottom: 20px;
	border-radius: 8px;
	overflow: hidden;
}

.branch-360-image {
	margin-bottom: 20px;
}

.branch-map-button {
	margin-top: 20px;
	text-align: center;
}

.branch-map-button .button {
	width: 100%;
	display: inline-flex;
	justify-content: center;
	align-items: center;
	gap: 5px;
}

/* Sections */
.branch-section {
	margin-bottom: 40px;
}

.branch-section-title {
	font-size: 1.4em;
	margin-bottom: 20px;
	color: #d4af37;
	border-left: 4px solid #d4af37;
	padding-left: 15px;
	text-transform: uppercase;
}

/* Contact Box */
.contact-row {
	font-size: 1.1em;
	margin-bottom: 12px;
	padding-bottom: 12px;
	border-bottom: 1px solid #f0f0f0;
}

.contact-row:last-child {
	border-bottom: none;
}

.contact-row strong {
	color: #333;
	margin-right: 10px;
}

/* Hours Table */
.branch-hours-table {
	width: 100%;
	border-collapse: collapse;
	background: #fff;
	border: 1px solid #eee;
}

.branch-hours-table th,
.branch-hours-table td {
	padding: 12px 15px;
	border-bottom: 1px solid #eee;
	text-align: left;
}

.branch-hours-table th {
	background: #f4f4f4;
	font-weight: 600;
	color: #333;
}

/* Transport */
.transport-item {
	margin-bottom: 20px;
}

.transport-icon {
	font-weight: 700;
	color: #333;
	margin-bottom: 5px;
	display: flex;
	align-items: center;
	gap: 8px;
}

.transport-detail {
	color: #555;
	padding-left: 28px;
	line-height: 1.6;
}

/* Responsive */
@media (max-width: 900px) {
	.branch-content-wrapper {
		flex-direction: column;
	}
	
	.branch-left-col {
		flex: none;
		max-width: 100%;
		width: 100%;
	}
}
</style>

<?php
get_footer();