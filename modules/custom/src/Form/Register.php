<?php
/**
 * @file
 * Contains \Drupal\custom\Form\Register\Form.
 */
namespace Drupal\custom\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
class Register extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
		$vid = 'country';
		$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
		foreach ($terms as $term) {
		 $term_data[$term->tid] = $term->name;
		}		
		//kint(\Drupal::entityTypeManager()->getStorage('taxonomy_term'));
    $form['username'] = array(
      '#type' => 'textfield',
      '#title' => t('Candidate Name:'),
      '#required' => TRUE,
			'#prefix' => "<div id='user-name-result'></div>",
      '#ajax' => array(
       'callback' => "::checkUserNameValidation",
				'effect' => 'fade',
         'event' => 'change',
          'progress' => array(
             'type' => 'throbber',
             'message' => NULL,
          ),       
      ),
    );
    $form['useremail'] = array(
      '#type' => 'email',
      '#title' => t('Email ID:'),
      '#required' => TRUE,
			'#prefix' => "<div id='user-email-result'></div>",
      '#ajax' => array(
       'callback' => "::checkUserEmailValidation",
				'effect' => 'fade',
         'event' => 'change',
          'progress' => array(
             'type' => 'throbber',
             'message' => NULL,
          ),       
      ),      
    );
    $form['usergender'] = array (
      '#type' => 'select',
      '#title' => ('Gender'),
      '#options' => array(
        'Female' => t('Female'),
        'male' => t('Male'),
      ),
    );
    $form['user_country'] = array(
    	'#type' => 'select',
      '#title' => ('Country'),
      '#options' => $term_data,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state) {
	  // if (strlen($form_state->getValue('candidate_number')) < 10) {
	  //   $form_state->setErrorByName('candidate_number', $this->t('Mobile number is too short.'));
	  // }
	   if ($form_state->getValue('username') != '' ) {
				$val = db_select('custom', 'c')
				  ->fields('c', array('name'))
				  ->condition('name', $form_state->getValue('username'))
				  ->execute()
				  ->fetchAllAssoc('name');
			  if(!empty($val)){
			  	$text = 'User is already exist. Please try different username';
			  	$form_state->setErrorByName('username', $this->t($text));
			  }	   	
	   }	
	   if ($form_state->getValue('useremail') != '' ) {
				$val = db_select('custom', 'c')
				  ->fields('c', array('email'))
				  ->condition('email', $form_state->getValue('useremail'))
				  ->execute()
				  ->fetchAllAssoc('email');
			  if(!empty($val)){
			  	$text = 'User email is already exist. Please try different username';
			  	$form_state->setErrorByName('useremail', $this->t($text));
			  }	   	
	   }	     
	}
	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
	  $name = $form_state->getValue('username'); 
	  $email = $form_state->getValue('useremail');   
	  $gender = $form_state->getValue('usergender');      
	  $country = $form_state->getValue('user_country');
		$field = array(
			'name' =>  $name,
			'email' => $email,
			'gender' =>  $gender,
			'country' => $country,
		);
		db_insert('custom')
      ->fields($field)
      ->execute();
		drupal_set_message("succesfully saved");
		//require_once __DIR__ . '/google-api/vendor/autoload.php';					  
	}

	public function checkUserEmailValidation(array $form, FormStateInterface $form_state) {
	   $ajax_response = new AjaxResponse();
	 
	  // Check if User or email exists or not
	   
	   if ($form_state->getValue('username') != '') {
				$val = db_select('custom', 'c')
				  ->fields('c', array('email'))
				  ->condition('email', $form_state->getValue('useremail'))
				  ->execute()
				  ->fetchAllAssoc('email');
			  if(!empty($val)){
			  	$text = 'User email is already exist. Please try different email';
			  }	   	
	   $ajax_response->addCommand(new HtmlCommand('#user-email-result', $text));
	   return $ajax_response;
	   }
	}

	public function checkUserNameValidation(array $form, FormStateInterface $form_state) {
	   $ajax_response = new AjaxResponse();
	 
	  // Check if User or email exists or not
	   
	   if ($form_state->getValue('username') != '') {
				$val = db_select('custom', 'c')
				  ->fields('c', array('name'))
				  ->condition('name', $form_state->getValue('username'))
				  ->execute()
				  ->fetchAllAssoc('name');
			  if(!empty($val)){
			  	$text = 'User is already exist. Please try different username';
			  }	   	
	   $ajax_response->addCommand(new HtmlCommand('#user-name-result', $text));
	   return $ajax_response;
	   }
	}			
}