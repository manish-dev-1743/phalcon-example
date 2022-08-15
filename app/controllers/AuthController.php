<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;




class AuthController extends ControllerBase
{

    public function indexAction()
    {
    }

    public function loginAction()
    {



         $data = ($_REQUEST);
        // var_dump($data);
            // $data = ['status'=>'200','message'=>'request Accept'];
        //    $data = ['status'=>200,'email' => $this->request->getPost('email')];
            return json_encode($data);


        var_dump($this->request->getPost());
        die();
        // return $this->response->setJsonContent('Hello');
        if ($this->session->has('auth')) {
            $this->view->disable();
            $this->response->redirect('/');
        }

        if (!$this->request->getPost()) {
            $this->response->setJsonContent(array('status' => 'ERROR', 'messages' => $this->flash->error('No login made')));
            return $this->response;
        }
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        return $this->response->setJsonContent('error','test error');
        if (($email == '' || $email == null) || ($password == '' || $password == null)) {
            $this->response->setJsonContent(array('status' => 'ERROR', 'messages' => $this->flash->error('Email/Password is required.')));
            return $this->response;
        }

        $user = Users::findFirstByEmail($email);
        if (!$user) {
            $this->response->setJsonContent(array('status' => 'ERROR', 'messages' => $this->flash->error('User not found.')));
            return $this->response;
        }
        if ($this->security->checkHash($password, $user->password)) {
            //login 
            if ($user->role == 'Super Admin' || $user->role == 'Admin') {
                $this->session->set('auth', $user->id);
                $this->response->setJsonContent(array('status' => 'SUCCESS', 'id' => $this->session->get('auth'), 'loc' => 'dashboard'));
                return $this->response;
            } else {
                $this->view->disable();
                $this->session->set('auth', $user->id);
                $this->response->setJsonContent(array('status' => 'SUCCESS', 'id' => $this->session->get('auth'), 'loc' => '/'));
                return $this->response;
            }
        } else {

            $this->response->setJsonContent(array('status' => 'ERROR', 'messages' => $this->flash->error('Incorrect Password. Please try again.')));
            return $this->response;
        }
    }

    public function logoutAction()
    {
        # code...
        $this->session->destroy('auth');
        return $this->response->redirect('/');
    }




    public function adduserAction()
    {
        $user = new Users();


        if (!$this->request->getPost()) {
            return $this->response->setJsonContent('No Request was made.');
        }


        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');



        $password = $this->request->getPost('password');
        $re_pass = $this->request->getPost('re_pass');
        if ($password == $re_pass) {

            $user->name = $name;
            $user->email = $email;
            $user->password = $this->security->hash($password);
            $user->role = 'User';

            if ($user->save()) {

                $this->response->setJsonContent(array('status' => 'SUCCESS', 'message' => $name . ' user successfully created.'));
                return $this->response;
            } else {
                $messages = $user->getMessages();

                $this->response->setJsonContent(array('status' => 'ERROR', 'message' => $this->flash->error($messages[0])));
                return $this->response;
            }
        } else {


            $this->response->setJsonContent(array('status' => 'ERROR', 'message' => $this->flash->error('Password Mismatch!')));
            return $this->response;
        }
    }

    public function forgotpassAction()
    {

        // ini_set("mail.add_x_header", TRUE);

        if ($this->session->has("auth")) {
            $this->view->disable();
            return $this->response->redirect("/");
        }
        if (!$this->request->getPost()) {
            $this->response->setJsonContent(array('status' => 'ERROR', 'message' => $this->flash->error('No Request was made.')));
            return $this->response;
        }

        $email = $this->request->getPost('email');


        $user = Users::findFirstByEmail($email);
        if (!$user) {

            $this->response->setJsonContent(array('status' => 'ERROR', 'message' => $this->flash->error('User not Found.')));
            return $this->response;
        }

        //Load Composer's autoloader
        // var_dump(getcwd());die();

        require '../vendor/autoload.php';
        $mail = new PHPMailer(true);
        $ran = rand(0000, 9999);
        $user->ver_code = $ran;
        $user->save();
        try {
            //Server settings

            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'devmanish1743@gmail.com';                     //SMTP username
            $mail->Password   = 'aazuptowhotabrdm';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('devmanish1743@gmail.com', 'Manish Dev');
            $mail->addAddress($email);     //Add a recipient

            // //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Password Reset Code';
            $mail->Body    = 'Hey ' . $user->name . ', Your Password reset code is <b>' . $ran . '</b>';
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            if ($mail->send()) {

                $this->response->setJsonContent(array('status' => 'SUCCESS', 'id' => $user->id));
                return $this->response;
            }
        } catch (Exception $e) {
            var_dump("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            die();
        }
    }



    public function codeverAction()
    {
        # code...
        if ($this->request->isPost()) {

            if ($this->request->isAjax()) {
                $id = $this->request->getPost('id');
                $code = $this->request->getPost('code');
                $user = Users::findFirstById($id);
                if ($user->ver_code != $code) {
                    $this->response->setJsonContent(array('status' => 'ERROR', 'message' => $this->flash->error('Validation code is Invalid. Try again')));
                    return $this->response;
                } else {
                    $user->ver_code = 0;
                    $user->save();
                    $this->response->setJsonContent(array('status' => 'SUCCESS', 'message' => $this->flash->success('Success! Enter Your New Password'), 'id' => $id));
                    return $this->response;
                }
            }
        }
    }


    public function chpaAction()
    {
        if ($this->request->getPost()) {

            $id = $this->request->getPost('id');
            $newpass = $this->request->getPost('newpass');
            $repass = $this->request->getPost('repass');

            $user = Users::findFirstById($id);

            if ($newpass == $repass) {
                $user->password = $this->security->hash($newpass);
                if ($user->save()) {
                    $this->response->setJsonContent(array('status' => 'SUCCESS', 'message' => $this->flash->success('Password Successfully Changed!!!')));
                    return $this->response;
                }
            } else {
                $this->response->setJsonContent(array('status' => 'ERROR', 'message' => $this->flash->error('Password Mismatched!!!')));
                return $this->response;
            }
        }
    }
}
