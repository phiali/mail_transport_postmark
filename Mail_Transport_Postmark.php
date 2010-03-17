<?php
/**
 * Postmark Mail Transport Class for Zend_Mail
 *
 * Copyright 2010, Alistair Phillips, http://the.0gravity.co.uk/universe/php/zend/mail_transport_postmark/
 *
 * @author Alistair Phillips (alistair@0gravity.co.uk)
 * @copyright Copyright 2010, Alistair Phillips
 * @version 0.1
 *
 */
 
class Mail_Transport_Postmark extends Zend_Mail_Transport_Abstract
{
    /**
     * API key required by Postmark
     *
     * @var string
     */
    private $_apiKey = '';
    
    public function __construct( $apiKey = '' )
    {
        if ( empty( $apiKey ) ) {
            throw new Exception( __CLASS__ . ' must be instantiated with a API key' );
        }
        
        $this->_apiKey = $apiKey;
    }
    
    public function _sendMail()
    {
        // Retrieve the headers and appropriate keys we need to construct our mail
        $headers = $this->_mail->getHeaders();
        
        $to = array();
        if ( array_key_exists( 'To', $headers ) ) {
            reset($headers['To']);
            foreach($headers['To'] as $key => $val ) {
                if( empty($key) || $key != 'append' )
                {
                    $to[] = $val;
                }
            }
            reset($headers['To']);
        }
        
        $cc = array();
        if ( array_key_exists( 'Cc', $headers ) ) {
            reset($headers['Cc']);
            foreach($headers['Cc'] as $key => $val ) {
                if( empty($key) || $key != 'append' )
                {
                    $cc[] = $val;
                }
            }
            reset($headers['Cc']);
        }
        
        $from = array();
        if ( array_key_exists( 'From', $headers ) ) {
            reset($headers['From']);
            foreach($headers['From'] as $key => $val ) {
                if( empty($key) || $key != 'append' )
                {
                    $from[] = $val;
                }
            }
            reset($headers['From']);
        }
        
        $replyto = array();
        if ( array_key_exists( 'Reply-To', $headers ) ) {
            reset($headers['Reply-To']);
            foreach($headers['Reply-To'] as $key => $val ) {
                if( empty($key) || $key != 'append' )
                {
                    $replyto[] = $val;
                }
            }
            reset($headers['Reply-To']);
        }
        
        $postData = array(
            'From'     => implode( ',', $from ),
            'To'       => implode( ',', $to ),
            'Cc'       => implode( ',', $cc ),
            'Subject'  => $this->_mail->getSubject(),
            'ReplyTo'  => implode( ',', $replyto ),
        );
        
        // We first check if the relevant content exists (returned as a Zend_Mime_Part)
        if ( $this->_mail->getBodyText() ) {
            $postData['TextBody'] = $this->_mail->getBodyText()->getContent();
        }
        
        if ( $this->_mail->getBodyHtml() ) {
            $postData['HtmlBody'] = $this->_mail->getBodyHtml()->getContent();
        }
        
        require_once 'Zend/Http/Client.php';
        $client = new Zend_Http_Client();
        $client->setUri( 'http://api.postmarkapp.com/email' );
        $client->setMethod( Zend_Http_Client::POST );
        $client->setHeaders( array(
            'Accept' => 'application/json',
            'X-Postmark-Server-Token' => $this->_apiKey
        ));
        $client->setRawData( json_encode( $postData ), 'application/json' );
        $response = $client->request();
        
        if ( $response->getStatus() != 200 ) {
            throw new Exception( 'Mail not sent - Postmark returned ' . $response->getStatus() . ' - ' . $response->getMessage() );
        }
    }
}