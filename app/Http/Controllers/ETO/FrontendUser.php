<?php

switch( $action ) {
  case 'get':

    $user = $etoAPI->getUser();

    $data['user'] = $user;
    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'save':

    $post = new \stdClass();
    $post->title = (string)$etoPost['title'];
    $post->firstName = (string)$etoPost['firstName'];
    $post->lastName = (string)$etoPost['lastName'];
    $post->mobileNumber = (string)$etoPost['mobileNumber'];
    $post->telephoneNumber = (string)$etoPost['telephoneNumber'];
    $post->emergencyNumber = (string)$etoPost['emergencyNumber'];
    $post->address = (string)$etoPost['address'];
    $post->city = (string)$etoPost['city'];
    $post->postcode = (string)$etoPost['postcode'];
    $post->state = (string)$etoPost['state'];
    $post->country = (string)$etoPost['country'];
    $post->email = (string)$etoPost['email'];

    $post->companyName = (string)$etoPost['companyName'];
    $post->companyNumber = (string)$etoPost['companyNumber'];
    $post->companyTaxNumber = (string)$etoPost['companyTaxNumber'];

    $post->password = (string)$etoPost['password'];
    $post->passwordConfirmation = (string)$etoPost['passwordConfirmation'];
    $post->departments = (array)$etoPost['departments'];
    $post->avatar_delete = (string)$etoPost['avatar_delete'];

    $etoAPI->saveUser($post);

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'register':

    $post = new \stdClass();
    $post->firstName = (string)$etoPost['firstName'];
    $post->lastName = (string)$etoPost['lastName'];
    $post->email = (string)$etoPost['email'];

    $post->profileType = (string)$etoPost['profileType'];
    $post->companyName = (string)$etoPost['companyName'];
    $post->companyNumber = (string)$etoPost['companyNumber'];
    $post->companyTaxNumber = (string)$etoPost['companyTaxNumber'];

    $post->password = (string)$etoPost['password'];
    $post->passwordConfirmation = (string)$etoPost['passwordConfirmation'];
    $post->terms = (string)$etoPost['terms'];
    $post->baseURL = (string)$etoPost['baseURL'];

    $etoAPI->register($post);

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'registerActivation':

    $post = new \stdClass();
    $post->token = (string)$etoPost['token'];
    $post->baseURL = (string)$etoPost['baseURL'];

    $etoAPI->registerActivation($post);

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'registerResend':

    $post = new \stdClass();
    $post->email = (string)$etoPost['email'];
    $post->baseURL = (string)$etoPost['baseURL'];

    $etoAPI->registerResend($post);

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'login':

    $post = new \stdClass();
    $post->email = (string)$etoPost['email'];
    $post->password = (string)$etoPost['password'];

    $etoAPI->login($post);

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'password':

    $post = new \stdClass();
    $post->email = (string)$etoPost['email'];
    $post->baseURL = (string)$etoPost['baseURL'];

    $etoAPI->password($post);

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'passwordNew':

    $post = new \stdClass();
    $post->token = (string)$etoPost['token'];
    $post->password = (string)$etoPost['password'];
    $post->passwordConfirmation = (string)$etoPost['passwordConfirmation'];

    $etoAPI->passwordNew($post);

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
  case 'logout':

    $etoAPI->logout();

    $data['success'] = $etoAPI->success;
    $data['message'] = $etoAPI->message;

  break;
}
