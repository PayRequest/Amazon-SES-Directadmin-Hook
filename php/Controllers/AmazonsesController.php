<?php
/**
 * Amazon SES controller that allows the user to control, add and delete their amazon ses verification
 * @author: Payrequest
 * @copyright: 2021 - Attribution-ShareAlike 4.0 International
 * @license: https://creativecommons.org/licenses/by-sa/4.0/
 */

namespace DirectAdmin\AmazonSes\Controllers;

use Exception;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials as Credentials;

class AmazonsesController
{
	private $_config           = [];
	private $_domains          = [];
    private $_basePath         = NULL;
	
    private $sesClient = NULL;
    
	/**
	 * Constructor
	 *
	 * @return void
	 * @throws Exception
	 */
    public function __construct()
    {
        $this->init();
    }
	
	/**
	 * Init and gather all data and domains
	 *
	 * @return void
	 * @throws Exception
	 */
    public function init()
    {
        $this->_basePath = dirname(dirname(__DIR__));
	    if(!file_exists($this->_basePath . '/data/amazon.conf'))
		    throw new Exception('data/amazon.conf doesn\'t exist! Please reinstall the module!');
	    $_config = file_get_contents($this->_basePath . '/data/amazon.conf');
	    preg_match_all('/(.*)=(.*)/m', $_config, $configs, PREG_SET_ORDER, 0);
	    foreach($configs as $match)
		    $this->_config[$match[1]] = $match[2];

        if(!empty($this->_config)){
	        $domainlist = ['admin'=>[]];
	        $_domains = urldecode($this->api_get('/CMD_API_SHOW_USER_DOMAINS?user=admin')).'&';
	        preg_match_all('/(.*)=(.*)\&/mU', $_domains, $domains, PREG_SET_ORDER, 0);
	        foreach($domains as $domain)
	        	$domainlist['admin'][$domain[1]] = 'pending';
        	$_users = $this->api_get('/CMD_API_SHOW_ALL_USERS');
	        preg_match_all('/(.*)=(.*)/m', $_users, $matches, PREG_SET_ORDER, 0);
	        foreach($matches as $match) {
		        $domainlist[$match[2]] = [];
		        $_domains = urldecode($this->api_get('/CMD_API_SHOW_USER_DOMAINS?user='.$match[2])).'&';
		        preg_match_all('/(.*)=(.*)\&/mU', $_domains, $domains, PREG_SET_ORDER, 0);
		        foreach($domains as $domain)
			        $domainlist[$match[2]][$domain[1]] = 'pending';
	        }
	        $this->_domains = $domainlist;
        }else{
            throw new Exception('No config data available!');
        }
    }
	
	/**
	 * @param       $cmd
	 * @param false $post
	 *
	 * @return false|mixed|string
	 */
	function api_get($cmd, $post = false){
		$is_post = false;
		if(is_array($post)){
			$is_post = true;
			$str = '';
			foreach($post as $var => $value)
			{
				if(strlen($str) > 0)
					$str .= '&';
				$str .= $var . '=' . urlencode($value);
			}
			$post = $str;
		}
		
		$headers = [];
		$headers['Host'] = '127.0.0.1:' . $_ENV['SERVER_PORT'];
		$headers['Cookie'] = 'session=' . $_ENV['SESSION_ID'] . '; key=' . $_ENV['SESSION_KEY'];
		
		if($is_post){
			$headers['Content-type'] = 'application/x-www-form-urlencoded';
			$headers['Content-length'] = strlen($post);
		}
		
		$send = ($is_post ? 'POST ' : 'GET ') . $cmd . " HTTP/1.1\r\n";
		foreach($headers as $var => $value)
			$send .= $var . ': ' . $value . "\r\n";
		$send .= "\r\n";
		if($is_post && strlen($post) > 0)
			$send .= $post . "\r\n\r\n";
		
		$res = @fsockopen('127.0.0.1', $_SERVER['SERVER_PORT'],$sock_errno, $sock_errstr, 3);
		if($sock_errno || $sock_errstr)
			return false;
		
		fputs($res, $send, strlen($send));
		
		$result = '';
		while(!feof($res))
			$result .= fgets($res, 32768);
		@fclose($res);
		
		$data = explode("\r\n\r\n", $result, 2);
		if(count($data) == 2)
			return $data[1];
		return false;
	}
	
	/**
	 * Return config data for admin
	 *
	 * @return array
	 */
	public function getConfig(): array
	{
    	return $this->_config;
    }
	
	/**
	 * @param array $_config
	 */
	public function setConfig($_config = []){
		$_config = array_intersect_key($_config, array_flip(['access_key_id','secret_access_key','region']));
		$this->_config = $_config;
		ob_start();
		foreach($this->_config as $key=>$value)
			echo $key.'='.$value.PHP_EOL;
	    $config = ob_get_clean();
	    file_put_contents($this->_basePath . '/data/amazon.conf',$config);
    }
	
	/**
	 * Return all domains of given user
	 *
	 * @param string $username
	 *
	 * @return array|mixed
	 */
	public function getDomains($username = ''): array
	{
		if(empty($username))
			return $this->_domains;
		elseif(!isset($this->_domains[$username]))
			return [];
		else
			return $this->_domains[$username];
	}
	
	/**
	 * Return all domains of given user
	 *
	 * @param string $username
	 *
	 * @return array|mixed
	 */
	public function getDomainsAndStatus($username = ''): array
	{
		$this->connectAws();
		$domains = $this->getDomains($username);
		$result = $this->sesClient->getIdentityVerificationAttributes(['Identities' => array_keys($domains)]);
		foreach($domains as $domain=>$status)
			$domains[$domain] = $result['VerificationAttributes'][$domain]['VerificationStatus']=='success'?'success':'pending';
		return $domains;
	}
	
	public function connectAws(){
		$credentials = new Credentials($this->_config['access_key_id'], $this->_config['secret_access_key']);
		$this->sesClient = new \Aws\Ses\SesClient(['version' => 'latest', 'credentials' => $credentials, 'region' => $this->_config['region']]);
	}
	
    /**
     * Exec
     *
     * @param $command
     *
     * @return bool
     */
    public function _exec($command): bool
    {
        if ($output = shell_exec($command))
        {
            return $output;
        }

        return FALSE;
    }
	
	/**
	 * @param $domain
	 *
	 * @return string
	 */
	public function verifyFirstStep($domain): string
	{
		$this->connectAws();
		$result = $this->SesClient->getIdentityVerificationAttributes(['Identities' => [$domain]]);
		return $result['VerificationAttributes'][$domain]['VerificationStatus']=='success'?'success':'pending';
	}
	
	/**
	 * @param $domain
	 *
	 * @return bool
	 */
	public function setFirstStep($domain): bool
	{
		$token = $this->getFirstStep($domain);
		$this->api_get('/CMD_API_DNS_CONTROL',[
			'domain' => $domain,
			'action' => 'add',
			'type' => 'TXT',
			'name' => '_amazonses.'.$domain.'.',
			'value' => $token
		]);
		return true;
	}
	
	/**
	 * @param $domain
	 *
	 * @return bool
	 */
	public function setDkims($domain): bool
	{
		$rawdns = $this->api_get('/CMD_API_DNS_CONTROL?domain='.$domain);
		$dkims = $this->getDkims($domain);
		foreach($dkims as $name=>$value){
			if(strpos($name,$rawdns)===false){
				$this->api_get('/CMD_API_DNS_CONTROL',[
					'domain' => $domain,
					'action' => 'add',
					'type' => 'CNAME',
					'name' => $name,
					'value' => $value
				]);
			}
		}
		return true;
	}
	
	/**
	 * @param $domain
	 *
	 * @return mixed
	 */
	public function getFirstStep($domain)
	{
		$this->connectAws();
		try {
			$this->sesClient->verifyDomainIdentity(['Domain' => $domain]);
			$this->sesClient->verifyDomainDkim(['Domain' => $domain]);
			$result = $this->sesClient->getIdentityVerificationAttributes(['Identities' => [$domain]]);
		}catch(Exception $e){
			return $e->getMessage();
		}
		return $result['VerificationAttributes'][$domain]['VerificationToken'];
	}
	
	/**
	 * @param $domain
	 *
	 * @return array
	 */
	public function getDkims($domain): array
	{
		$this->connectAws();
		$this->sesClient->verifyDomainIdentity(['Domain' => $domain]);
		$result = $this->sesClient->verifyDomainDkim(['Domain' => $domain]);
		$response = [];
		foreach($result->get('DkimTokens') as $dkim){
				$response[$dkim.'._domainkey'] = $dkim.'.dkim.amazonses.com';
		}
		return $response;
	}
}