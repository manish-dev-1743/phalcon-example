<?php

declare(strict_types=1);

class IndexController extends ControllerBase
{
    public function initialize()
    {
        $cmp = COMPANY::findFirstById(3);
        if ($this->session->has("auth")) {
            $i = $this->session->get("auth");
            $logg = Users::findFirstById($i);

            // $userLocation = $logg->userlocation;
            // var_dump($logg->userLocation->id);
            // die();
            // echo"<pre>";
            // var_dump($userLocation);
            // echo"<pre>";
            
            // die();
            $this->logg = $logg;
        }
        $this->cmp = $cmp;

        $this->view->cmp = $cmp;
    }
    public function indexAction()
    {
        $home = HOME::findFirstById(1);

        $this->view->home = $home;
        if ($this->session->has('auth')) {
            // $usr=Users::findFirstById($this->session->get('auth'));
            
            $this->view->user = $this->logg;
        }
    }
    public function myprofileAction()
    {
        $home = HOME::findFirstById(1);

        $this->view->home = $home;
        if (!$this->session->has('auth')) {
            // $usr=Users::findFirstById($this->session->get('auth'));
            // $this->_redirect('/booking/room#availableResults');
            
            $this->response->redirect('/');
            
            
        }
        $this->view->user = $this->logg;
    }



    public function updatemyprofileAction()
    {
        if (!$this->session->has('auth')) {
            $this->response->redirect('/');
        }
        if($this->request->getPost()){
            if($this->request->isAjax()){
                $email = $this->request->getPost('email');
                $id = $this->session->get('auth');
                $user= Users::findFirstById($id);
                $user->name = $this->request->getPost('name');
                $tempem=Users::findByEmail($email);
                if(!$tempem){
                    $user->email = $this->request->getPost('email');
                    $user->save();
                    $this->response->setJsonContent(array('status' => 'SUCCESS', 'message' => $this->flash->success('User Successfully Updated.')));
                    return $this->response;
                }else{
                    $this->response->setJsonContent(array('status' => 'ERROR', 'message' => $this->flash->error('Email already exists. Try different one.')));
                    return $this->response;                   
                }   
            }
        }    
    }
}
