<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml"
      style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
<head>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</head>
<body bgcolor="#f6f6f6"
      style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0;">
<!-- body -->
<table class="body-wrap" bgcolor="#f6f6f6"
       style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;">
	<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
		<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>
		<td class="container" bgcolor="#FFFFFF"
		    style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 960px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">
			<!-- content -->
			<div class="content"
			     style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 960px; display: block; margin: 0 auto; padding: 0;">
				<table
					style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
					<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
						<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;"><?php echo $greeting; ?></p>

							<?php
							if ( ! empty( $events ) ):
								$i = 0;
								foreach ( $events as $event ):
									?>
									<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
										<?php
										//Go through the copy array, keeping in mind we only have 3 unqiue texts
										if ( $event->enabled == 1 ) {
											printf( $enabled_copy[ $i % 3 ]['copy'], $event->name, $home_url, $event->date_start );
										} else {
											printf( $disabled_copy[ $i % 3 ]['copy'], $event->name, $home_url, $event->date_start );
										}
										?>
									</p>
									<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
										<img
											src="<?php printf( 'https://api.pagelr.com/capture/javascript?width=300&height=210&delay=2900&uri=%s', $home_url . '?display_event=' . $event->id ); ?>"
											width="300px" height="210px">
									</p>
									<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 35px; padding: 0;">
										<a href="<?php echo $home_url . '/wp-admin/admin.php?page=perfect-decorations-for-occasions&slug=perfect-decorations-for-occasions&view=feeds&context=all&display=' . $event->id; ?>">
											<?php
											if ( $event->enabled == 1 ) {
												echo $enabled_copy[ $i % 3 ]['link'];
											} else {
												echo $disabled_copy[ $i % 3 ]['link'];
											}
											?>
										</a></p>
									<?php
									++ $i;
								endforeach;
							endif;
							if ( ! empty( $upcoming ) ):
								?>
								<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
									In the upcoming week our community around the world will also celebrate:</p>
								<?php
								foreach ( $upcoming as $event ):
									?>
									<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 35px; padding: 0;">
										<?php echo $event->name; ?> - Join in. <a
											href="<?php echo $home_url . '/wp-admin/admin.php?page=perfect-decorations-for-occasions&slug=perfect-decorations-for-occasions&view=feeds&context=all&display=' . $event->id; ?>">See
											decoration</a>
									</p>
								<?php
								endforeach;
							endif;
							?>
						</td>
					</tr>
				</table>
			</div>
			<!-- /content -->
		</td>
		<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>
	</tr>
</table>
<!-- /body --><!-- footer -->
<table class="footer-wrap"
       style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;">
	<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
		<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>
		<td class="container"
		    style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">
			<!-- content -->
			<div class="content"
			     style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">
				<table
					style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
					<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
						<td align="center"
						    style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.6; color: #666; font-weight: normal; margin: 0 0 10px; padding: 0;">
								Don't like these emails? <a
									href="<?php echo $home_url . 'wp-admin/admin.php?page=perfect-decorations-for-occasions&slug=perfect-decorations-for-occasions&view=settings'; ?>"
									style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; color: #999; margin: 0; padding: 0;">
									<unsubscribe
										style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
										Manage e-mail reminder settings here
									</unsubscribe>
								</a>.
							</p>
						</td>
					</tr>
				</table>
			</div>
			<!-- /content -->

		</td>
		<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>
	</tr>
</table>
<!-- /footer --></body>
</html>
