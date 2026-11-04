<?php
require '../config/app.php'; require_role('patient'); require '../config/payment.php'; header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['ok'=>false,'message'=>'Method not allowed']);exit;}check_csrf();$id=(int)($_POST['appointment_id']??0);$uid=user()['id'];$paymentId=trim($_POST['razorpay_payment_id']??'');$orderId=trim($_POST['razorpay_order_id']??'');$signature=trim($_POST['razorpay_signature']??'');
$st=$pdo->prepare("SELECT p.id,p.amount,p.gateway_order_id,a.id appointment_id FROM payments p JOIN appointments a ON a.id=p.appointment_id WHERE p.appointment_id=? AND a.patient_id=?");$st->execute([$id,$uid]);$p=$st->fetch();if(!$p||$p['gateway_order_id']!==$orderId){echo json_encode(['ok'=>false,'message'=>'Payment order could not be verified.']);exit;}
$expected=hash_hmac('sha256',$orderId.'|'.$paymentId,$razorpayKeySecret);if(!hash_equals($expected,$signature)){echo json_encode(['ok'=>false,'message'=>'Invalid payment signature.']);exit;}
$up=$pdo->prepare("UPDATE payments SET gateway_payment_id=?,gateway_signature=?,transaction_id=?,method='Online',status='Paid',paid_at=NOW() WHERE id=?");$up->execute([$paymentId,$signature,$paymentId,$p['id']]);
echo json_encode(['ok'=>true]);
