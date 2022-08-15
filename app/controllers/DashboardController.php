<?php
declare(strict_types=1);

class DashboardController extends ControllerBase
{
    

    public function initialize()
    {
        $cmp = COMPANY::findFirstById(3);
        
        $i = $this->session->get("auth");
        $user = Users::findFirstById($i);
        $this->user = $user;
        $this->cmp = $cmp;
        $this->view->user = $user;
        $this->view->cmp = $cmp;
    }
    
    public function indexAction()
    {
        if (!$this->session->has("auth")) {
            //user is not logged 
            // so we redirect them to the login page
            return $this->response->redirect("login");
        }
        // var_dump($this->session->get('auth'));
        // die();
        $users = count(Users::find());
       
        $this->view->users = $users;
        
        


    }
    public function userlistAction()
    {
        # code...
        if (!$this->session->has("auth")) {
            //user is not logged 
            // so we redirect them to the login page
            return $this->response->redirect("login");
        }
        $data = Users::find();
        $this->view->datas = $data;
    }

    public function deleteAction($id){
        $data = Users::findById($id);
        if($data){
            $data->delete();
            return $this->response->redirect("userlist");
        }
    }

    public function updateAction($id)
    {
        # code...
        if($this->request->isAjax() == true) {
            $data = Users::findById($id);
            if($data){
                return $this->response->setJsonContent($data);
            }
        }
    }
    public function editAction($id)
    {
        # code...
        $user = Users::findFirstById($id);
        
        if($this->request->isAjax() == true) {
            if($this->request->getPost("password") != '' && $this->request->getPost("password") != null){
                
                if($this->request->getpost('password') == $this->request->getpost('re_pass') ){
                    $pass = $this->request->getPost("password");
                    $user->password = $this->security->hash($pass); 
                }else{
                  
                    return $this->response->setJsonContent('Password Mismatch.');
                }
            }
            
            $user->name = $this->request->getPost("name");
            $user->email = $this->request->getPost("email");
            $user->role = $this->request->getPost("role");
            $user->update();
            return $this->response->setJsonContent('User successfully updated.');
        }else{
          
            return $this->response->setJsonContent('No AJAX was called.');
        }
    }

    public function adduserAction()
    {
        # code...
        if($this->request->isAjax() == true){
            $user = new Users();
            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $re_pass = $this->request->getPost('re_pass');
            $role = $this->request->getPost('role');
             
            if($password == $re_pass){

                $user->name = $name;
                $user->email = $email;
                $user->password = $this->security->hash($password);
                $user->role = $role;
                $user->save();

                return $this->response->setJsonContent('User successfully added.');
            }else
            return $this->response->setJsonContent('Password mismatched.');
        }
    }

    public function myprofileAction()
    {
        # code...
        if (!$this->session->has("auth")) {
            //user is not logged 
            // so we redirect them to the login page
            return $this->response->redirect("login");
        }
    }

    public function userEditAction()
    {

        $id = $this->session->get('auth');
        $cmp = $this->cmpdetails();
        $user = Users::findFirstById($id);
        if($this->request->isAjax() == true){
           
            if($this->request->getPost('email')){
                $user->email = $this->request->getPost('email');
                $user->save();
                if($user->profile_url == null || $user->profile_url == ''){
                    $profileimg = 'img/profile/dummy.png';
                }else
                    $profileimg = 'img/profile/'.$user->profile_url;
               
                // $this->session->set('auth',$user->id);
                return $this->response->setJsonContent('Email updated.');
            }else if($this->request->getPost('name')){
                
                //storing file in server and database.
                $upload_dir ='../public/img/profile/';
                if(!empty($_FILES["file"]["name"])){
                    $fileName = basename($_FILES["file"]["name"]); 
                    $targetFilePath = $upload_dir . $fileName; 
                    if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){ 
                        $uploadedFile = $fileName; 
                    }
                
                    $user->name = $this->request->getPost('name');
                    $user->profile_url = $uploadedFile;
                    $user->save();
                    $profileimg = 'img/profile/'.$user->profile_url;
                    // $this->session->set('auth',['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'profile_img' => $profileimg, 'cmp' => $cmp]);
                    return $this->response->setJsonContent('Profile Succesfully Changed.');
                }else{
                    $user->name = $this->request->getPost('name');
                    $user->save();
                    if($user->profile_url == null || $user->profile_url == ''){
                        $profileimg = 'img/profile/dummy.png';
                    }else
                        $profileimg = 'img/profile/'.$user->profile_url;
                    // $this->session->set('auth',['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'profile_img' => $profileimg, 'cmp' => $cmp]);
                    return $this->response->setJsonContent('Name updated successfully.');
                }

            }
        }


    }


    public function changepassAction()
    {
        # code...
        if (!$this->session->has("auth")) {
            //user is not logged 
            // so we redirect them to the login page
            return $this->response->redirect("login");
        }
    }

    public function chpassAction()
    {
        # code...
        $id = $this->session->get('auth')['id'];

        $user = Users::findFirstById($id);
        
        $curr_pass = $this->request->getPost('old_pass');
        $new_pass = $this->request->getPost('new_pass');
        $re_pass = $this->request->getPost('re_pass');

        if ($this->security->checkHash($curr_pass, $user->password)){
            if($new_pass == $re_pass){
                $user->password = $this->security->hash($new_pass);
                $success = $user->save();
                if($success)
                    return $this->response->setJSONContent('Password changed successful !!!');
                else
                    return $this->response->setJSONContent('Something went wrong !!!');
            }else
                return $this->response->setJSONContent('Password Mismatched !!!');
        }else
            return $this->response->setJSONContent('Please enter correct current password !!!');
    }


    public function companydetailsAction(){
       
        if ($this->user->role != 'Super Admin') {
            //user is not logged 
            // so we redirect them to the login page
            $this->flashSession->error('Contact Developer to work on it.');
            return $this->response->redirect("login");
        }
        $this->view->setLayoutsDir( 'layouts/dashboard_header' );
        $this->view->setLayoutsDir( 'layouts/dashboard_footer' );
    }

    public function addcmpAction()
    {
        # code...

        // $countfiles = count();
        // var_dump($_FILES['file']['name']);
        // die();
        
        $i = 0;
        $cmp = COMPANY::findFirstById(3);
        if( $this->request->getPost('cname') != null)
            $cmp->name = $this->request->getPost('cname');

        if( $this->request->getPost('email') != null)
            $cmp->email = $this->request->getPost('email');

        if( $this->request->getPost('fb_url') != null)
            $cmp->facebook = $this->request->getPost('fb_url');

        if( $this->request->getPost('tw_url') != null)
            $cmp->twitter = $this->request->getPost('tw_url');

        if( $this->request->getPost('yt_url') != null)
            $cmp->youtube = $this->request->getPost('yt_url');

        if( $this->request->getPost('address') != null)
            $cmp->address = $this->request->getPost('address');

        if( $this->request->getPost('phone') != null)
            $cmp->phone = $this->request->getPost('phone');

        // $upload_dir ='../public/img/company/';
        if( $this->request->hasFiles() == true)
        {
            
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?

                
                    $i++;
                    $Name      = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $FileName  = "../public/img/company/" . $Name;
                    
                    // move file to needed path";
                    if(!$file->moveTo($FileName))
                    {   
                        // something  goes worong
                        $this->flash->error("An error occurred while uploading the document.");
                    }
                    if($i == 1){
                        $cmp->logo_url = $Name;
                    }else
                        $cmp->favicon =$Name;
                 
            }
        }


        $cmp->save();
        $this->flashSession->success('Company successfully updated.');
        $this->response->redirect("company"); 
        $this->view->disable();
    }

    public function destroyAction($id)
    {
        # code...
        $user = Users::findFirstById($id);
        $user->delete();
        $this->session->destroy();
        
    }


    public function homepageAction()
    {
        # code...
    }

    public function addhmpAction()
    {
        # code...
        
        $i = 0;
        $h = HOME::findFirstById(1);
        if($this->request->getPost('title') != null)
            $h->title = $this->request->getPost('title');

        if($this->request->getPost('coltitle') != null)
            $h->coltitle = $this->request->getPost('coltitle');

        if($this->request->getPost('fav_item') != null)
            $h->fav_item = $this->request->getPost('fav_item');
        

        // $upload_dir ='../public/img/company/';
        if( $this->request->hasFiles() == true)
        {
            
            foreach( $this->request->getUploadedFiles() as $file)
            {              
                // is it a valid extension?

                
                    $i++;
                    $Name      = preg_replace("/[^A-Za-z0-9.]/", '-', $file->getName() );
                    $FileName  = "../public/frontend/img/fr_imgs/" . $Name;
                    
                    // move file to needed path";
                    if(!$file->moveTo($FileName))
                    {   
                        // something  goes worong
                        $this->flash->error("An error occurred while uploading the document.");
                    }
                    if($i == 1){
                        $h->bgimg = $Name;
                    }else
                        $h->ovelayimg =$Name;
                 
            }
        }


        $h->save();
        $this->flashSession->success('Homepage successfully updated.');
        $this->response->redirect("homepage"); 
        $this->view->disable();
    }
}

