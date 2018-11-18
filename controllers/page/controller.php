<?php

class Pagecontroller {

	private $registry;
	private $urlBits;
	
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		if( $directCall == true )
		{
			$this->viewPage();
		}
	}
	
	private function viewPage()
	{
		global $caption, $deb;
  			
    $this->urlBits = $this->registry->getURLBits();

    require_once( FRAMEWORK_PATH . 'models/page/model.php');
		$this->model = new Pagemodel( $this->registry, $this->registry->getURLPath() );
	  if( $this->model->isValid() )
		{
			$pageData = $this->model->getProperties();
 	  	$this->registry->getObject('template')->getPage()->addTag( 'actionSearch', 'products/search');
      $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'main.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->dataToTags( $pageData, '' );
			$this->registry->getObject('template')->getPage()->setTitle( $pageData['title'] );
			
		}
		else
		{
      $urlPath = $this->registry->getURLPath();

      switch ($urlPath)
      {      
        case 'login':
          if ( isset($_POST['login']) && trim($_POST['login']) != $caption['sendpsw']){
            if ( $this->registry->getObject('authenticate')->isLoggedIn() ) {
              header("HTTP/1.1 301 Moved Permanently");
              header("Location: " . $config['siteurl']. 'products');
              header("Connection: close");      			
  	   	    }else{
        			$this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['LoggedFault'] );
  	   	    	$this->registry->getObject('template')->getPage()->addTag('pagecontent', $this->registry->getObject('authenticate')->getLoginFailureReason() );
  	   	    }
          }

          if ( isset($_POST['register']) ){
            $text = $this->registry->getObject('fce')->randomString( 5 );
            $_SESSION['confirmPicture'] = $text;
            $this->registry->getObject('fce')->randomPicture('./temp/picture.png', $text, 20);
            $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'register.tpl.php', 'footer.tpl.php');
          }

          if ( isset($_POST['sendPassword']) ){
            $text = $this->registry->getObject('fce')->randomString( 5 );
            $_SESSION['confirmPicture'] = $text;
            $this->registry->getObject('fce')->randomPicture('./temp/picture.png', $text, 20);
            $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'sendlogin.tpl.php', 'footer.tpl.php');
          }
          
          break;
        case 'logout':
    			$this->registry->getObject('template')->getPage()->addTag('pageheading', $caption['LogoutNow'] );
   	    	$this->registry->getObject('template')->getPage()->addTag('pagecontent', $caption['LogoutNowMsg'] );
          break;
        default:
          switch ($this->urlBits[0]) 
          {  
            case 'order':
              $this->viewOrder();
              break;
            case 'news':
              $this->singNews();
              break;
            default:
              $this->pageNotFound();
          }
      }
		}
	}
	
	private function viewOrder()
	{
		global $deb;
		
    if ( isset($this->urlBits[1]) && isset($this->urlBits[2]) )
    {
      // Autenticate Access
      
      $orderNo = $this->registry->getObject('db')->sanitizeData( $this->urlBits[1] );
      $orderHash = $this->registry->getObject('db')->sanitizeData( $this->urlBits[2] );
      $sql = "SELECT o.*, os.complete 
                FROM orders o, order_statuses os
                WHERE os.ID=o.status AND o.order_no=$orderNo AND o.password_hash='$orderHash' 
                  AND os.complete=0";
      $this->registry->getObject('db')->executeQuery( $sql );
      if( $this->registry->getObject('db')->numRows() == 0 ){
      	$this->pageNotFound();
      }else{	
        $orderDate = $this->registry->getObject('db')->getRows();
        $orderID = $orderDate['ID'];
         	
        $this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'account/order.tpl.php','footer.tpl.php');
    		require_once( FRAMEWORK_PATH . 'models/order/model.php');
    		
        $order = new Order( $this->registry, $orderID );
        if( $order->isValid() )
    		{
          $this->registry->getObject('template')->getPage()->addTag('order', $orderID );	
  				$this->registry->getObject('template')->getPage()->addTag('orderNo', $order->getOrderNo() );
          $this->registry->getObject('template')->getPage()->addTag('items', array( 'SQL', $order->getItemsCache() ) );
  				$this->registry->getObject('template')->getPage()->addTag('date_placed', $order->getDatePlaced() );
  				$this->registry->getObject('template')->getPage()->addTag('status', $order->getStatusName() );
  				$this->registry->getObject('template')->getPage()->addTag('pc', $order->getProductsCost() );
  				$this->registry->getObject('template')->getPage()->addTag('sc', $order->getShippingCost() );
  				$this->registry->getObject('template')->getPage()->addTag('toc', $order->getProductsCost() + $order->getShippingCost() );
  				$this->registry->getObject('template')->getPage()->addTag('shipping_method', $order->getShippingMethod() );
  				$this->registry->getObject('template')->getPage()->addTag('payment_method', $order->getPaymentMethod() );
  
          $data = $order->getData();
    			$this->registry->getObject('template')->getPage()->addTag('name', $data['deliveryAddress']['name'] );
    			$this->registry->getObject('template')->getPage()->addTag('address', $data['deliveryAddress']['address'] );
    			$this->registry->getObject('template')->getPage()->addTag('address2', $data['deliveryAddress']['address2'] );
    			$this->registry->getObject('template')->getPage()->addTag('city', $data['deliveryAddress']['city'] );
    			$this->registry->getObject('template')->getPage()->addTag('postcode', $data['deliveryAddress']['zipcode'] );
    			$this->registry->getObject('template')->getPage()->addTag('email', $data['deliveryAddress']['email'] );
    			$this->registry->getObject('template')->getPage()->addTag('phone', $data['deliveryAddress']['phone'] );
    		}
    		else
    		{
    			$this->pageNotFound();
    		}
      }
    }else
      $this->pageNotFound();  
  }

	private function singNews()
	{
		global $deb, $caption, $config;
    if ( isset( $_POST['news'] ) && isset( $_POST['news_email']) && $_POST['news_email'] != '' )
    {
      $email = $this->registry->getObject('db')->sanitizeData( $_POST['news_email'] ); 
      
      $sql = "SELECT * FROM users WHERE email='$email'";
      $this->registry->getObject('db')->executeQuery( $sql );
      if( $this->registry->getObject('db')->numRows() == 0){ 
        $this->registry->getObject('authenticate')->setSendNews( $email , 0);
      }
      $this->registry->getObject('db')->executeQuery( $sql );
      $users = $this->registry->getObject('db')->getRows();

      if( $users['news_hash'] == ''){
        $userUpd = array();
        $userUpd['news_hash'] = $this->registry->getObject('fce')->randomString( 60 );
        $this->registry->getObject('db')->updateRecords( 'users', $userUpd, 'ID=' . $users['ID']);
        $users['news_hash'] = $userUpd['news_hash']; 
      }
      
      switch (trim($_POST['news']))
      {
        case $caption['SingUp']:
          // Send Email for Confirm SingIn
          $this->registry->getObject('email')->init( 'template/email_user.tpl.php','singin');
          $this->registry->getObject('email')->from = $config['infoEmail'];
          $this->registry->getObject('email')->to = $email;
          $this->registry->getObject('email')->addFields($config,'cfg_');
          $this->registry->getObject('email')->addFields($users);
          $this->registry->getObject('email')->setSubject('subject');
          $this->registry->getObject('email')->send();
          $this->registry->redirectUser( 'products', $caption['SendNews'], $caption['AddEmailNews'], $admin = false );
          break;
        case $caption['SingOff']:
          // Send Email for Confirm SingOff
          $this->registry->getObject('email')->init( 'template/email_user.tpl.php','singout');
          $this->registry->getObject('email')->from = $config['infoEmail'];
          $this->registry->getObject('email')->to = $email;
          $this->registry->getObject('email')->addFields($config,'cfg_');
          $this->registry->getObject('email')->addFields($users);
          $this->registry->getObject('email')->setSubject('subject');
          $this->registry->getObject('email')->send();
          $this->registry->redirectUser( 'products', $caption['SendNews'], $caption['DelEmailNews'], $admin = false );
          break;
        default:        
          $this->pageNotFound();
          break;
      };
    }elseif( isset($this->urlBits[1]) && ($this->urlBits[1] != '') && isset($this->urlBits[2]) && ($this->urlBits[2] != '')) 
    {
      $hash = $this->urlBits[2];
      $sql = "SELECT * FROM users WHERE news_hash='$hash'";
      $this->registry->getObject('db')->executeQuery( $sql );
      $validUser = ( $this->registry->getObject('db')->numRows() == 1 )?1:0;
      if( $validUser == 1 ){
        $users = $this->registry->getObject('db')->getRows();
        switch( $this->urlBits[1] )
        {
          case 'singin':
            $this->registry->getObject('authenticate')->setSendNews( $users['email'] , 1);
            break;
          case 'singout':
            $this->registry->getObject('authenticate')->setSendNews( $users['email'] , 0);
            break;
        } 
        if ( $this->urlBits[1] == 'singin' )
          $this->registry->redirectUser( 'products', $caption['SendNews'], $caption['addToNews'], $admin = false );
        else
          $this->registry->redirectUser( 'products', $caption['SendNews'], $caption['remFromNews'], $admin = false );
      }else
        $this->pageNotFound();
    }else
      $this->registry->redirectUser( 'products', $caption['Error'], $caption['ErrorNews1'], $admin = false );
  }
  
  private function pageNotFound()
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', '404.tpl.php', 'footer.tpl.php');
	}
}
?>