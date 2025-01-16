<?php
session_start();
$login="user";
$password="password";
$admin_login="admin";
$admin_password="admin";
$_SESSION['loggedin']=false;
$data = json_decode(file_get_contents('php://input'), true);
if(isset($data)){
    $user_login=$data['login'];
    $user_password=$data['password'];
}else{
    $response=['success'=>false,'message'=> 'Данные не переданы.'];
    echo json_encode($response);
    die();
}
if($user_login===$login&&$user_password===$password){
    $_SESSION['loggedin']=true;
    $_SESSION['userType']="worker";
    $response=[
        'success'=>true,
        'user'=>'worker'
    ];
    echo json_encode($response);
}else if($user_login===$admin_login&&$user_password===$admin_password){
    $_SESSION['loggedin']=true;
    $_SESSION['userType']="admin";
    $response=[
        'success'=>true,
        'user'=>'admin'
    ];
    echo json_encode($response);
}else{
    $response=[
        'success'=>false,
        'message'=>"Данные неверны. Проверьте логин и пароль."
    ];
    echo json_encode($response);
    die();
}
?>
