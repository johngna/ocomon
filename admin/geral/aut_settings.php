<?php
/* Copyright 2020 Flávio Ribeiro

This file is part of OCOMON.

OCOMON is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

OCOMON is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foobar; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */ session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
	$_SESSION['session_expired'] = 1;
	echo "<script>top.window.location = '../../index.php'</script>";
	exit;
}

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 1);

$_SESSION['s_page_admin'] = $_SERVER['PHP_SELF'];

$tags = array(
	htmlentities("<script>"), 
	htmlentities("</script>"),
	"<script>", 
	"</script>"
);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/switch_radio.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/datatables/datatables.min.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/css/my_datatables.css" />
	<link rel="stylesheet" type="text/css" href="../../includes/components/summernote/summernote-bs4.css" />

	<title>OcoMon&nbsp;<?= VERSAO; ?></title>
</head>

<body>
	<div class="container">
		<div id="idLoad" class="loading" style="display:none"></div>
	</div>

	

	<div id="divResult"></div>


	<div class="container-fluid">
		<h4 class="my-4"><i class="fas fa-envelope-open-text text-secondary"></i>&nbsp;<?= TRANS('TTL_CONFIG_TICKET_AUT'); ?></h4>
		<div class="modal" id="modal" tabindex="-1" style="z-index:9001!important">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div id="divDetails">
					</div>
				</div>
			</div>
		</div>

		

		<?php
		if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
			echo $_SESSION['flash'];
			$_SESSION['flash'] = '';
		}

		$query = "SELECT * FROM config_aut ";
		if (isset($_GET['ID'])) {
			$query .= "WHERE ID=" . $_GET['ID'] . "";
		}
		$query .= " ORDER BY ID";
		$resultado = $conn->query($query);
		$registros = $resultado->rowCount();

		if ((!isset($_GET['action'])) && !isset($_POST['submit'])) {

			if ($registros == 0) {
				echo message('info', '', TRANS('NO_RECORDS_FOUND'), '', '', true);
			} else {

		?>



			<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-light">
							<h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-triangle text-secondary"></i>&nbsp;<?= TRANS('REMOVE'); ?></h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<?= TRANS('CONFIRM_REMOVE'); ?> <span class="j_param_id"></span>?
						</div>
						<div class="modal-footer bg-light">
							<button type="button" class="btn btn-secondary" data-dismiss="modal"><?= TRANS('BT_CANCEL'); ?></button>
							<button type="button" id="deleteButton" class="btn"><?= TRANS('BT_OK'); ?></button>
						</div>
					</div>
				</div>
			</div>



				
				<?= message('info', TRANS('TXT_IMPORTANT'), '<hr>' . TRANS('HELPER_TASK_SCHEDULER'), '', '', true); ?>
			
				
				<button class="btn btn-sm btn-primary" id="idBtIncluir" name="new"><?= TRANS("ACT_NEW"); ?></button><br /><br />

				<table id="table_lists" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">

					<thead>
						<tr class="header">
							<td class="line reply_to"><?= TRANS('AUT_ID'); ?></td>
							<td class="line event"><?= TRANS('AUT_MAIL'); ?></td>
							<td class="line from"><?= TRANS('AUT_NAME'); ?></td>
							<td class="line editar"><?= TRANS('BT_EDIT'); ?></td>
						</tr>
					</thead>
					<tbody>
						<?php

						foreach ($resultado->fetchall() as $row) {

							
						?>
							<tr>
								<td class="line"><?= $row['ID']; ?></td>
								<td class="line"><?= $row['MAIL_GET_ADDRESS']; ?></td>
								<td class="line"><?= $row['MAIL_GET_MAILBOX']; ?></td>
								<td class="line">
									<button type="button" class="btn btn-secondary btn-sm" onclick="redirect('<?= $_SERVER['PHP_SELF']; ?>?action=edit&ID=<?= $row['ID']; ?>')"><?= TRANS('BT_EDIT'); ?></button>
									<button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal('<?= $row['ID']; ?>')"><?= TRANS('REMOVE'); ?></button>
								</td>
							</tr>

						<?php
						}
						?>
					</tbody>
				</table>
			<?php
			}
		} else

		if ((isset($_GET['action']) && $_GET['action'] == "edit") && empty($_POST['submit'])) {

			$row = $resultado->fetch();
			?>
			<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
				<?= csrf_input(); ?>
				<!-- <div class="form-group row my-4">
					<label for="msg_from" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_EVENT'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="msg_from" name="msg_from" readonly value="<?= $row['msg_event']; ?>" required />
					</div>

					<label for="msg_from" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_FROM'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="msg_from" name="msg_from" value="<?= $row['msg_fromname']; ?>" required />
					</div>

					<label for="reply_to" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_REPLY_TO'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="reply_to" name="reply_to" value="<?= $row['msg_replyto']; ?>" required />
					</div>

					<label for="subject" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('SUBJECT'); ?></label>
					<div class="form-group col-md-10">
						<input type="text" class="form-control " id="subject" name="subject" value="<?= $row['msg_subject']; ?>" required />
					</div>

					<label for="variables" class="col-md-2 col-form-label col-form-label-sm text-md-right"></label>
					<div class="form-group col-md-10">
						<div class="accordion" id="accordionVariables">
							<div class="card">
								<div class="card-header" id="headingOne">
									<h2 class="mb-0">
										<button class="btn btn-block text-left font-weight-bold" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" onClick="blur();">
											<i class="fas fa-closed-captioning text-secondary"></i>&nbsp;<?= TRANS('OPT_ENVIRON_AVAIL'); ?>
										</button>
									</h2>
								</div>

								<div id="collapseOne" class="collapse " aria-labelledby="headingOne" data-parent="#accordionVariables">
									<div class="card-body bg-light">
										<?= nl2br(getEnvVars($conn)); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="w-100"></div>


					<label for="body_content" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_HTML_MSG'); ?></label>
					<div class="form-group col-md-10">
						<textarea name="body_content" id="body_content" class="form-control" rows="4"><?=  toHtml($row['msg_body']); ?></textarea>

					</div>

					<label for="alternative_content" class="col-md-2 col-form-label col-form-label-sm text-md-right"><?= TRANS('OPT_ALTERNATE_MSG'); ?></label>
					<div class="form-group col-md-10">
						<textarea name="alternative_content" id="alternative_content" class="form-control" rows="4"><?= $row['msg_altbody']; ?></textarea>
					</div>


					<input type="hidden" name="cod" value="<?= $_GET['cod']; ?>">
					<input type="hidden" name="action" id="action" value="edit">

					<div class="row w-100"></div>
					<div class="form-group col-md-8 d-none d-md-block">
					</div>
					<div class="form-group col-12 col-md-2 ">
						<button type="submit" id="idSubmit" name="submit" value="edit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
					</div>
					<div class="form-group col-12 col-md-2">
						<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
					</div>

				</div> -->
				<div class="form-group row mt-2 mb-4">
                    

                    <div class="form-group col-md-12 mt-4">
                        <p class="border-bottom text-secondary font-weight-bold ml-4"><?= TRANS('CONNECTION'); ?></p>
                    </div>
                    <div class="w-100"></div>

                    
                    <label for="mail_account" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_MAIL_ADDRESS_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('MAIL_ADDRESS_TO_FETCH')); ?></label>
                    <div class="form-group col-md-3">
                        <input type="text" class="form-control" name="mail_account" id="mail_account" required value="<?= $row['MAIL_GET_ADDRESS']; ?>" placeholder="<?= TRANS('MAIL_ADDRESS_TO_FETCH'); ?>" />
                    </div>
                    <label for="account_password" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('MAIL_PASS_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('MAIL_PASS_TO_FETCH')); ?></label>
                    <div class="form-group col-md-3">
                        <input type="password" class="form-control" name="account_password" id="account_password" value="" placeholder="<?= TRANS('PASSWORD_EDIT_PLACEHOLDER'); ?>" />
                    </div>

                    <label for="imap_address" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('IMAP_ADDRESS_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('IMAP_ADDRESS_TO_FETCH')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control" name="imap_address" id="imap_address" required value="<?= $row['MAIL_GET_IMAP_ADDRESS']; ?>" placeholder="<?= TRANS('IMAP_ADDRESS_TO_FETCH'); ?>" />
                    </div>


                    <label for="mail_port" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('PORT_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('PORT_TO_FETCH')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control" name="mail_port" id="mail_port" required value="<?= $row['MAIL_GET_PORT']; ?>" placeholder="<?= TRANS('PORT_TO_FETCH'); ?>" />
                    </div>

                    <label class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_HAS_SSL_CERT'); ?>"><?= firstLetterUp(TRANS('HAS_SSL_CERT')); ?></label>
                    <div class="form-group col-md-9">

                        <div class="switch-field">
                            <?php
                            $yesChecked = ($row['MAIL_GET_CERT'] == 1 ? "checked" : "");
                            $noChecked = ($row['MAIL_GET_CERT'] == 0 ? "checked" : "");
                            ?>
                            <input type="radio" id="ssl_cert" name="ssl_cert" value="yes" <?= $yesChecked; ?> />
                            <label for="ssl_cert"><?= TRANS('YES'); ?></label>
                            <input type="radio" id="ssl_cert_no" name="ssl_cert" value="no" <?= $noChecked; ?> />
                            <label for="ssl_cert_no"><?= TRANS('NOT'); ?></label>
                        </div>
                    </div>


                    <label class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('TEST_CONNECTION'); ?>"><?= firstLetterUp(TRANS('TEST_CONNECTION')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="button" class="btn btn-success" name="testConnection" id="testConnection" value="<?= TRANS('TEST_CONNECTION'); ?>">
                    </div>
                    
                    <div class="w-100"></div>


                    <div class="form-group col-md-12 mt-4">
                        <p class="border-bottom text-secondary font-weight-bold ml-4"><?= TRANS('MESSAGE_TREATMENT'); ?></p>
                    </div>
                    <div class="w-100"></div>

                    <label for="mailbox" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_MAILBOX_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('MAILBOX_TO_FETCH')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control" name="mailbox" id="mailbox" required value="<?= $row['MAIL_GET_MAILBOX']; ?>" placeholder="<?= TRANS('MAILBOX_TO_FETCH'); ?>" />
                    </div>
                    <label for="subject_has" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_SUBJECT_FILTER_CONTAINS'); ?>"><?= firstLetterUp(TRANS('SUBJECT_FILTER_CONTAINS')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control" name="subject_has" id="subject_has" value="<?= $row['MAIL_GET_SUBJECT_CONTAINS']; ?>" placeholder="<?= TRANS('SUBJECT_FILTER_CONTAINS'); ?>" />
                    </div>
                    <label for="body_has" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_BODY_FILTER_CONTAINS'); ?>"><?= firstLetterUp(TRANS('BODY_FILTER_CONTAINS')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control" name="body_has" id="body_has" value="<?= $row['MAIL_GET_BODY_CONTAINS']; ?>" placeholder="<?= TRANS('BODY_FILTER_CONTAINS'); ?>" />
                    </div>

                    <label for="days_since" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_DAYS_SINCE_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('DAYS_SINCE_TO_FETCH')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="number" class="form-control" name="days_since" id="days_since" min="1" max="5" required value="<?= $row['MAIL_GET_DAYS_SINCE']; ?>" placeholder="<?= TRANS('DAYS_SINCE_TO_FETCH'); ?>" />
                    </div>

                    <label class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_MARK_FETCHED_AS_SEEN'); ?>"><?= firstLetterUp(TRANS('MARK_FETCHED_AS_SEEN')); ?></label>
                    <div class="form-group col-md-9">

                        <div class="switch-field">
                            <?php
                            $yesChecked = ($row['MAIL_GET_MARK_SEEN'] == 1 ? "checked" : "");
                            $noChecked = ($row['MAIL_GET_MARK_SEEN'] == 0 ? "checked" : "");
                            ?>
                            <input type="radio" id="mark_seen" name="mark_seen" value="yes" <?= $yesChecked; ?> />
                            <label for="mark_seen"><?= TRANS('YES'); ?></label>
                            <input type="radio" id="mark_seen_no" name="mark_seen" value="no" <?= $noChecked; ?> />
                            <label for="mark_seen_no"><?= TRANS('NOT'); ?></label>
                        </div>
                    </div>

                    <label for="move_to" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_MAILBOX_TO_MOVE_TO'); ?>"><?= firstLetterUp(TRANS('MAILBOX_TO_MOVE_TO')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control" name="move_to" id="move_to" required value="<?= $row['MAIL_GET_MOVETO']; ?>" placeholder="<?= TRANS('MAILBOX_TO_MOVE_TO'); ?>" />
                    </div>


                    <div class="form-group col-md-12 mt-4">
                        <p class="border-bottom text-secondary font-weight-bold ml-4"><?= TRANS('TICKETS_TREATMENT'); ?></p>
                    </div>
                    <div class="w-100"></div>
                    <label for="system_user" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_SYSTEM_USER_TO_OPEN_TICKETS'); ?>"><?= firstLetterUp(TRANS('SYSTEM_USER_TO_OPEN_TICKETS')); ?></label>
                    <div class="form-group col-md-9">

                        <SELECT class="form-control" name="system_user" id="system_user" required>
                        <?php
                            $users = getUsers($conn, null, [1,2]);
                            foreach ($users as $user) {
                                ?>
                                <option value="<?= $user['login']; ?>"
                                    <?= ($user['login'] == $userToOpenTickets ? " selected" : ""); ?>
                                >
                                    <?= $user['login']; ?>
                                </option>

                                <?php
                            }
                        ?>
                        </SELECT>
                    </div>

                    <label for="area" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_AUTO_TICKETING_AREA'); ?>"><?= firstLetterUp(TRANS('SERVICE_AREA')); ?></label>
                    <div class="form-group col-md-9">

                        <SELECT class="form-control" name="area" id="area" required>
                            <option value=""><?= TRANS('SEL_SELECT'); ?></option>
                        <?php
                            $areas = getAreas($conn, 0, 1, 1);
                            foreach ($areas as $area) {
                                ?>
                                <option value="<?= $area['sis_id']; ?>"
                                    <?= ($area['sis_id'] == $row['API_TICKET_BY_MAIL_AREA'] ? " selected" : ""); ?>
                                >
                                    <?= $area['sistema']; ?>
                                </option>

                                <?php
                            }
                        ?>
                        </SELECT>
                    </div>

                    <label for="status" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_AUTO_TICKETING_STATUS'); ?>"><?= firstLetterUp(TRANS('COL_STATUS')); ?></label>
                    <div class="form-group col-md-9">

                        <SELECT class="form-control" name="status" id="status" required>
                        <?php
                            $statusList = getStatus($conn);
                            foreach ($statusList as $status) {
                                ?>
                                <option value="<?= $status['stat_id']; ?>"
                                    <?= ($status['stat_id'] == $row['API_TICKET_BY_MAIL_STATUS'] ? " selected" : ""); ?>
                                >
                                    <?= $status['status']; ?>
                                </option>

                                <?php
                            }
                        ?>
                        </SELECT>
                    </div>


                    <label for="input_tags" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_AUTOMATIC_INPUT_TAGS'); ?>"><?= firstLetterUp(TRANS('INPUT_TAGS')); ?></label>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control" name="input_tags" id="input_tags" value="<?= $row['API_TICKET_BY_MAIL_TAG']; ?>" placeholder="<?= TRANS('ADD_OR_REMOVE_INPUT_TAGS'); ?>" />
                        <div class="invalid-feedback">
							<?= TRANS('ERROR_MIN_SIZE_OF_TAGNAME'); ?>
						</div>
                    </div>


                    <label for="opening_channel" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('OPENING_CHANNEL'); ?>"><?= firstLetterUp(TRANS('OPENING_CHANNEL')); ?></label>
                    <div class="form-group col-md-9">

                        <SELECT class="form-control" name="opening_channel" id="opening_channel" required>
                        <?php
                            $channels = getChannels($conn, null, 'restrict');
                            foreach ($channels as $channel) {
                                ?>
                                <option value="<?= $channel['id']; ?>"
                                    <?= ($channel['id'] == $row['API_TICKET_BY_MAIL_CHANNEL'] ? " selected" : ""); ?>
                                >
                                    <?= $channel['name']; ?>
                                </option>

                                <?php
                            }
                        ?>
                        </SELECT>
                    </div>

                </div>

				<div class="row">
					<div class="form-group col-12 col-md-2 ">

                        <input type="hidden" name="ID" id="ID" value="<?= $row['ID']; ?>">
                        <input type="hidden" name="action" id="action" value="edit">
                        <button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
                    </div>
                    <div class="form-group col-12 col-md-2">
                        <button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
                    </div>

					<div class="form-group col-12 col-md-2">
						<input type="button" class="btn btn-success btn-block" name="testAutomation" id="testAutomation" value="<?= TRANS('TEST_AUTOMATION'); ?>">
						</div>

					</div>

			</form>
		<?php
		}
		?>


		<?php 
		
		if ((isset($_GET['action']) && $_GET['action'] == "new") && empty($_POST['submit'])) {

		
		?>
		<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" id="form">
			<?= csrf_input(); ?>
			
			<div class="form-group row mt-2 mb-4">
				

				<div class="form-group col-md-12 mt-4">
					<p class="border-bottom text-secondary font-weight-bold ml-4"><?= TRANS('CONNECTION'); ?></p>
				</div>
				<div class="w-100"></div>

				
				<label for="mail_account" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_MAIL_ADDRESS_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('MAIL_ADDRESS_TO_FETCH')); ?></label>
				<div class="form-group col-md-3">
					<input type="text" class="form-control" name="mail_account" id="mail_account" required value="<?= $row['MAIL_GET_ADDRESS']; ?>" placeholder="<?= TRANS('MAIL_ADDRESS_TO_FETCH'); ?>" />
				</div>
				<label for="account_password" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('MAIL_PASS_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('MAIL_PASS_TO_FETCH')); ?></label>
				<div class="form-group col-md-3">
					<input type="password" class="form-control" name="account_password" id="account_password" value="" placeholder="<?= TRANS('PASSWORD_EDIT_PLACEHOLDER'); ?>" />
				</div>

				<label for="imap_address" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('IMAP_ADDRESS_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('IMAP_ADDRESS_TO_FETCH')); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control" name="imap_address" id="imap_address" required value="<?= $row['MAIL_GET_IMAP_ADDRESS']; ?>" placeholder="<?= TRANS('IMAP_ADDRESS_TO_FETCH'); ?>" />
				</div>


				<label for="mail_port" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('PORT_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('PORT_TO_FETCH')); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control" name="mail_port" id="mail_port" required value="<?= $row['MAIL_GET_PORT']; ?>" placeholder="<?= TRANS('PORT_TO_FETCH'); ?>" />
				</div>

				<label class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_HAS_SSL_CERT'); ?>"><?= firstLetterUp(TRANS('HAS_SSL_CERT')); ?></label>
				<div class="form-group col-md-9">

					<div class="switch-field">
						<?php
						$yesChecked = ($row['MAIL_GET_CERT'] == 1 ? "checked" : "");
						$noChecked = ($row['MAIL_GET_CERT'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="ssl_cert" name="ssl_cert" value="yes" <?= $yesChecked; ?> />
						<label for="ssl_cert"><?= TRANS('YES'); ?></label>
						<input type="radio" id="ssl_cert_no" name="ssl_cert" value="no" <?= $noChecked; ?> />
						<label for="ssl_cert_no"><?= TRANS('NOT'); ?></label>
					</div>
				</div>


				<label class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('TEST_CONNECTION'); ?>"><?= firstLetterUp(TRANS('TEST_CONNECTION')); ?></label>
				<div class="form-group col-md-9">
					<input type="button" class="btn btn-success" name="testConnection" id="testConnection" value="<?= TRANS('TEST_CONNECTION'); ?>">
				</div>
				
				<div class="w-100"></div>


				<div class="form-group col-md-12 mt-4">
					<p class="border-bottom text-secondary font-weight-bold ml-4"><?= TRANS('MESSAGE_TREATMENT'); ?></p>
				</div>
				<div class="w-100"></div>

				<label for="mailbox" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_MAILBOX_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('MAILBOX_TO_FETCH')); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control" name="mailbox" id="mailbox" required value="<?= $row['MAIL_GET_MAILBOX']; ?>" placeholder="<?= TRANS('MAILBOX_TO_FETCH'); ?>" />
				</div>
				<label for="subject_has" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_SUBJECT_FILTER_CONTAINS'); ?>"><?= firstLetterUp(TRANS('SUBJECT_FILTER_CONTAINS')); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control" name="subject_has" id="subject_has" value="<?= $row['MAIL_GET_SUBJECT_CONTAINS']; ?>" placeholder="<?= TRANS('SUBJECT_FILTER_CONTAINS'); ?>" />
				</div>
				<label for="body_has" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_BODY_FILTER_CONTAINS'); ?>"><?= firstLetterUp(TRANS('BODY_FILTER_CONTAINS')); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control" name="body_has" id="body_has" value="<?= $row['MAIL_GET_BODY_CONTAINS']; ?>" placeholder="<?= TRANS('BODY_FILTER_CONTAINS'); ?>" />
				</div>

				<label for="days_since" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_DAYS_SINCE_TO_FETCH'); ?>"><?= firstLetterUp(TRANS('DAYS_SINCE_TO_FETCH')); ?></label>
				<div class="form-group col-md-9">
					<input type="number" class="form-control" name="days_since" id="days_since" min="1" max="5" required value="<?= $row['MAIL_GET_DAYS_SINCE']; ?>" placeholder="<?= TRANS('DAYS_SINCE_TO_FETCH'); ?>" />
				</div>

				<label class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_MARK_FETCHED_AS_SEEN'); ?>"><?= firstLetterUp(TRANS('MARK_FETCHED_AS_SEEN')); ?></label>
				<div class="form-group col-md-9">

					<div class="switch-field">
						<?php
						$yesChecked = ($row['MAIL_GET_MARK_SEEN'] == 1 ? "checked" : "");
						$noChecked = ($row['MAIL_GET_MARK_SEEN'] == 0 ? "checked" : "");
						?>
						<input type="radio" id="mark_seen" name="mark_seen" value="yes" <?= $yesChecked; ?> />
						<label for="mark_seen"><?= TRANS('YES'); ?></label>
						<input type="radio" id="mark_seen_no" name="mark_seen" value="no" <?= $noChecked; ?> />
						<label for="mark_seen_no"><?= TRANS('NOT'); ?></label>
					</div>
				</div>

				<label for="move_to" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_MAILBOX_TO_MOVE_TO'); ?>"><?= firstLetterUp(TRANS('MAILBOX_TO_MOVE_TO')); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control" name="move_to" id="move_to" required value="<?= $row['MAIL_GET_MOVETO']; ?>" placeholder="<?= TRANS('MAILBOX_TO_MOVE_TO'); ?>" />
				</div>


				<div class="form-group col-md-12 mt-4">
					<p class="border-bottom text-secondary font-weight-bold ml-4"><?= TRANS('TICKETS_TREATMENT'); ?></p>
				</div>
				<div class="w-100"></div>
				<label for="system_user" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_SYSTEM_USER_TO_OPEN_TICKETS'); ?>"><?= firstLetterUp(TRANS('SYSTEM_USER_TO_OPEN_TICKETS')); ?></label>
				<div class="form-group col-md-9">

					<SELECT class="form-control" name="system_user" id="system_user" required>
					<?php
						$users = getUsers($conn, null, [1,2]);
						foreach ($users as $user) {
							?>
							<option value="<?= $user['login']; ?>"
								<?= ($user['login'] == $userToOpenTickets ? " selected" : ""); ?>
							>
								<?= $user['login']; ?>
							</option>

							<?php
						}
					?>
					</SELECT>
				</div>

				<label for="area" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_AUTO_TICKETING_AREA'); ?>"><?= firstLetterUp(TRANS('SERVICE_AREA')); ?></label>
				<div class="form-group col-md-9">

					<SELECT class="form-control" name="area" id="area" required>
						<option value=""><?= TRANS('SEL_SELECT'); ?></option>
					<?php
						$areas = getAreas($conn, 0, 1, 1);
						foreach ($areas as $area) {
							?>
							<option value="<?= $area['sis_id']; ?>"
								<?= ($area['sis_id'] == $row['API_TICKET_BY_MAIL_AREA'] ? " selected" : ""); ?>
							>
								<?= $area['sistema']; ?>
							</option>

							<?php
						}
					?>
					</SELECT>
				</div>

				<label for="status" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_AUTO_TICKETING_STATUS'); ?>"><?= firstLetterUp(TRANS('COL_STATUS')); ?></label>
				<div class="form-group col-md-9">

					<SELECT class="form-control" name="status" id="status" required>
					<?php
						$statusList = getStatus($conn);
						foreach ($statusList as $status) {
							?>
							<option value="<?= $status['stat_id']; ?>"
								<?= ($status['stat_id'] == $row['API_TICKET_BY_MAIL_STATUS'] ? " selected" : ""); ?>
							>
								<?= $status['status']; ?>
							</option>

							<?php
						}
					?>
					</SELECT>
				</div>


				<label for="input_tags" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('HELPER_AUTOMATIC_INPUT_TAGS'); ?>"><?= firstLetterUp(TRANS('INPUT_TAGS')); ?></label>
				<div class="form-group col-md-9">
					<input type="text" class="form-control" name="input_tags" id="input_tags" value="<?= $row['API_TICKET_BY_MAIL_TAG']; ?>" placeholder="<?= TRANS('ADD_OR_REMOVE_INPUT_TAGS'); ?>" />
					<div class="invalid-feedback">
						<?= TRANS('ERROR_MIN_SIZE_OF_TAGNAME'); ?>
					</div>
				</div>


				<label for="opening_channel" class="col-md-3 col-form-label col-form-label-sm text-md-right" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="<?= TRANS('OPENING_CHANNEL'); ?>"><?= firstLetterUp(TRANS('OPENING_CHANNEL')); ?></label>
				<div class="form-group col-md-9">

					<SELECT class="form-control" name="opening_channel" id="opening_channel" required>
					<?php
						$channels = getChannels($conn, null, 'restrict');
						foreach ($channels as $channel) {
							?>
							<option value="<?= $channel['id']; ?>"
								<?= ($channel['id'] == $row['API_TICKET_BY_MAIL_CHANNEL'] ? " selected" : ""); ?>
							>
								<?= $channel['name']; ?>
							</option>

							<?php
						}
					?>
					</SELECT>
				</div>

			</div>

			<div class="row">
				<div class="form-group col-12 col-md-2 ">

					
					<input type="hidden" name="action" id="action" value="new">
					<button type="submit" id="idSubmit" name="submit" class="btn btn-primary btn-block"><?= TRANS('BT_OK'); ?></button>
				</div>
				<div class="form-group col-12 col-md-2">
					<button type="reset" class="btn btn-secondary btn-block" onClick="parent.history.back();"><?= TRANS('BT_CANCEL'); ?></button>
				</div>
				

				</div>

		</form>
		<?php
		}
		?>



	</div>

	<script src="../../includes/javascript/funcoes-3.0.js"></script>
	<script src="../../includes/components/jquery/jquery.js"></script>
	<!-- <script src="../../includes/components/bootstrap/js/bootstrap.min.js"></script> -->
	<script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
	<script type="text/javascript" charset="utf8" src="../../includes/components/datatables/datatables.js"></script>
	<!-- <script type="text/javascript" charset="utf8" src="../../includes/components/ckeditor/ckeditor.js"></script> -->
	<script src="../../includes/components/summernote/summernote-bs4.js"></script>
	<script src="../../includes/components/summernote/lang/summernote-pt-BR.min.js"></script>
	<script src="../../includes/components/jquery/jquery.amsify.suggestags-master/js/jquery.amsify.suggestags.js"></script>
	<script type="text/javascript">
		$(function() {

			$('#table_lists').DataTable({
				paging: true,
				deferRender: true,
				columnDefs: [{
						searchable: false,
						orderable: false,
						targets: ['editar']
					},
					{
						width: '35%',
						targets: ['msg_html']
					}
				],
				"language": {
					"url": "../../includes/components/datatables/datatables.pt-br.json"
				}
			});

			$('#idBtIncluir').on("click", function() {
				$('#idLoad').css('display', 'block');
				var url = '<?= $_SERVER['PHP_SELF'] ?>?action=new';
				$(location).prop('href', url);
			});


			if ($('#body_content').length > 0) {
				$('#body_content').summernote({

					toolbar: [
						['style', ['style']],
						['font', ['bold', 'underline', 'clear']],
						['fontname', ['fontname']],
						['fontsize', ['fontsize']],
						['color', ['color']],
						['para', ['ul', 'ol', 'paragraph']],
						['table', ['table']],
						['insert', ['link', 'picture', 'video']],
						['view', ['fullscreen', 'codeview', 'help']],
					],
					tabDisable: true,

					// placeholder: 'Hello Bootstrap 4',
					lang: 'pt-BR', // default: 'en-US'
					tabsize: 2,
					// height: 100,
					height: 300, // set editor height
					minHeight: null, // set minimum height of editor
					maxHeight: null, // set maximum height of editor
					focus: true // set focus to editable area after initializing summernote
				});
			}



			$('#idSubmit').on('click', function(e) {
				e.preventDefault();
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});

				$.ajax({
					url: './configaut_process.php',
					method: 'POST',
					// data: $('#form').serialize() + "&htmlHiddenContent=" + formatBar.getData(),
					data: $('#form').serialize(),
					dataType: 'json',
				}).done(function(response) {

					if (!response.success) {
						$('#divResult').html(response.message);
						if (response.field_id != "")
							$('#' + response.field_id).focus();
					} else {
						var url = '<?= $_SERVER['PHP_SELF'] ?>';
						$(location).prop('href', url);
						return false;
					}
				});
				return false;
			});


			$('#bt-cancel').on('click', function() {
				var url = '<?= $_SERVER['PHP_SELF'] ?>';
				$(location).prop('href', url);
			});

			$('#testConnection').on('click', function(e) {
				e.preventDefault();
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});
				$("#testConnection").prop("disabled", true);
				$("#idSubmit").prop("disabled", true);
				$("#testConnection").val('<?= TRANS('WAIT'); ?>');

				$.ajax({
					url: './test_imap_connection.php',
					method: 'POST',
					data: $('#form').serialize(),
					dataType: 'json',
				}).done(function(response) {

					if (!response.success) {
						$('#divResult').html(response.message);
						$('input, select, textarea').removeClass('is-invalid');
						if (response.field_id != "") {
							$('#' + response.field_id).focus().addClass('is-invalid');
						}
						$("#testConnection").prop("disabled", false);
						$("#idSubmit").prop("disabled", false);
							$("#testConnection").val('<?= TRANS('TEST_CONNECTION'); ?>');
						} else {
							$('#divResult').html(response.message);
							$('input, select, textarea').removeClass('is-invalid');
							$("#testConnection").prop("disabled", false);
							$("#idSubmit").prop("disabled", false);
							$("#testConnection").val('<?= TRANS('TEST_CONNECTION'); ?>');
							return false;
						}
					});
					return false;
				});




				$('#testAutomation').on('click', function(e) {
				e.preventDefault();
				var loading = $(".loading");
				$(document).ajaxStart(function() {
					loading.show();
				});
				$(document).ajaxStop(function() {
					loading.hide();
				});
				$("#testAutomation").prop("disabled", true);
				$("#idSubmit").prop("disabled", true);
				$("#testAutomation").val('<?= TRANS('WAIT'); ?>');

				$.ajax({
					url: './test_imap_automation.php',
					method: 'POST',
					data: $('#form').serialize(),
					dataType: 'json',
				}).done(function(response) {

					if (!response.success) {
						$('#divResult').html(response.message);
						$('input, select, textarea').removeClass('is-invalid');
						if (response.field_id != "") {
							$('#' + response.field_id).focus().addClass('is-invalid');
						}
						$("#testAutomation").prop("disabled", false);
						$("#idSubmit").prop("disabled", false);
							$("#testAutomation").val('<?= TRANS('TEST_AUTOMATION'); ?>');
						} else {
							$('#divResult').html(response.message);
							$('input, select, textarea').removeClass('is-invalid');
							$("#testAutomation").prop("disabled", false);
							$("#idSubmit").prop("disabled", false);
							$("#testAutomation").val('<?= TRANS('TEST_AUTOMATION'); ?>');
							return false;
						}
					});
					return false;
				});



		});


		function confirmDeleteModal(id) {
			$('#deleteModal').modal();
			$('#deleteButton').html('<a class="btn btn-danger" onclick="deleteData(' + id + ')"><?= TRANS('REMOVE'); ?></a>');
		}


		function deleteData(id) {

			var loading = $(".loading");
			$(document).ajaxStart(function() {
				loading.show();
			});
			$(document).ajaxStop(function() {
				loading.hide();
			});

			$.ajax({
				url: './configaut_process.php',
				method: 'POST',
				data: {
					ID: id,
					action: 'delete'
				},
				dataType: 'json',
			}).done(function(response) {
				var url = '<?= $_SERVER['PHP_SELF'] ?>';
				$(location).prop('href', url);
				return false;
			});
			return false;
			// $('#deleteModal').modal('hide'); // now close modal
		}

	</script>
</body>

</html>