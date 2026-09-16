<?php

switch( $action ) {
  case 'list':

    $bookings = $etoAPI->bookingList();

    $data['bookings'] = $bookings;
    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'details':

    $post = new \stdClass();
    $post->id = (int)$etoPost['id'];

    $booking = $etoAPI->bookingDetails($post);

    $data['booking'] = $booking;
    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;
    $data['action'] = $action;

  break;
  case 'cancel':

    $post = new \stdClass();
    $post->id = (int)$etoPost['id'];
    $post->baseURL = (string)$etoPost['baseURL'];

    $etoAPI->bookingCancel($post);

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'pay':



  break;
  case 'edit':



  break;
  case 'delete':



  break;
  case 'new':



  break;
  case 'invoice':

    $post = new \stdClass();
    $post->id = (int)$etoPost['id'];
    $post->download = (int)$etoPost['download'];
    $post->embed = (int)$etoPost['embed'];

    $invoice = $etoAPI->bookingInvoice($post);

    $data['invoice'] = $invoice;
    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'finish':



  break;
}
