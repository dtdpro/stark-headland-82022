<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp;

/**
 * Controller used to manage watson test pages
 *
 * @Route("/watson")
 *
 * @author Mike Amundsen <mike@dtdpro.com>
 */
class WatsonController extends Controller
{
    /**
     * @Route("/", name="watson_index")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function indexAction()
    {
        return $this->render('watson/index.html.twig');
    }

    /**
     * @Route("/text-to-speech", name="text_to_speech")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function textToSpeechAction()
    {
	    $username = $this->container->getParameter('watson_text-to-speech_username');
	    $password = $this->container->getParameter('watson_text-to-speech_password');

	    $cred_supplied = true;
	    if ($username == '') $cred_supplied = false;
	    if ($password == '') $cred_supplied = false;

        return $this->render('watson/textToSpeech.html.twig', array('cred_supplied'=>$cred_supplied));
    }

	/**
	 * @Route("/speek-to-me", name="sppek_to_me")
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
	 */
	public function speekToMeAction(Request $request)
    {
	    $username = $this->container->getParameter('watson_text-to-speech_username');
	    $password = $this->container->getParameter('watson_text-to-speech_password');

	    $text = urldecode($request->query->get('speech'));
	    $voice = urldecode($request->query->get('voice'));

	    $client = new GuzzleHttp\Client([
		    'auth' => [$username, $password],
		    'base_uri' => 'https://stream.watsonplatform.net',
		    'headers' => ['Content-Type'=>'application/json']
	    ]);
	    $res = $client->request('POST', '/text-to-speech/api/v1/synthesize?voice='.$voice,['body'=>json_encode(array("text"=>$text))]);
	    $response = new Response();
	    $response->headers->set('Content-Type', "audio/ogg");
	    $response->setContent($res->getBody());
	    return $response;
    }
}
