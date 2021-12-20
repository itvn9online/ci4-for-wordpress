<!--
<link rel="stylesheet" href="./admin/css/bootstrap.min.css" />
<link rel="stylesheet" href="./admin/css/bootstrap-responsive.min.css" />
-->
<!-- <link rel="stylesheet" href="./admin/css/maruti-login.css" /> -->
<div id="loginbox">
	<form action="./users/profile" method="POST" accept-charset="utf-8" class="form-vertical">
		<input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>" />
		<div class="control-group normal_text">
			<h3><?php echo $seo['title']; ?></h3>
		</div>
		<br>
		<div>
			<div class="row">
				<div class="col-4">Tài khoản</div>
				<div class="col-8"><?php echo $data['user_login']; ?></div>
			</div>
			<br>
			<div class="row">
				<div class="col-4">Email</div>
				<div class="col-8">
					<?php

					//
					echo $data[ 'user_email' ];

					// chức năng riêng dành cho admin
					if ( $current_user_id > 0 && isset( $session_data[ 'userLevel' ] ) && $session_data[ 'userLevel' ] > 0 ) {
						?>
					<a href="./<?php echo CUSTOM_ADMIN_URI; ?>">@</a>
					<?php
					}

					?>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-4 l40">Họ và tên</div>
				<div class="col-8">
					<div class="form-control">
						<input type="text" placeholder="Họ và tên" name="data[display_name]" value="<?php echo $data['display_name']; ?>">
					</div>
				</div>
			</div>
			<br>
		</div>
		<div class="form-actions text-center">
			<input type="submit" class="btn btn-success" value="Cập nhật" />
		</div>
	</form>
	<hr />
	<div>
		<div class="row">
			<div class="col-4">Ngày đăng ký</div>
			<div class="col-8"><?php echo $data['user_registered']; ?></div>
		</div>
		<br>
		<div class="row">
			<div class="col-4">Đăng nhập cuối</div>
			<div class="col-8"><?php echo $data['last_login']; ?></div>
		</div>
		<br>
		<div class="row">
			<div class="col-4">Cập nhật cuối</div>
			<div class="col-8"><?php echo $data['last_updated']; ?></div>
		</div>
	</div>
	<hr />
	<form action="./users/profile" method="POST" accept-charset="utf-8" class="form-vertical">
		<input type="hidden" name="<?php echo csrf_token(); ?>" value="<?php echo csrf_hash(); ?>" />
		<div class="control-group normal_text">
			<h3>Đổi mật khẩu đăng nhập</h3>
		</div>
		<br>
		<div>
			<div class="row">
				<div class="col-4 l40">Thay đổi mật khẩu</div>
				<div class="col-8">
					<div class="form-control">
						<input type="text" placeholder="Thay đổi mật khẩu" name="data[ci_pass]" value="" onfocus="$('.redcolor-if-pass-focus').addClass('redcolor');" onblur="$('.redcolor-if-pass-focus').removeClass('redcolor');">
					</div>
				</div>
			</div>
			<p class="text-center redcolor-if-pass-focus">* <em>Chỉ nhập mật khẩu khi bạn cần đổi mật khẩu đăng nhập</em>.</p>
		</div>
		<div class="form-actions text-center">
			<input type="submit" class="btn btn-success" value="Cập nhật" />
		</div>
	</form>
</div>
<br>
