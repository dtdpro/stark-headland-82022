<?php


namespace AppBundle\Controller;

use AppBundle\Entity\WebForm;
use AppBundle\Entity\WebFormSubmission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Controller used to manage web form contents in the public part of the site.
 *
 * @Route("/webform")
 */
class WebFormController extends Controller
{
    /**
     * @Route("/", name="webform_index", defaults={"page" = 1})
     * @Route("/page/{page}", name="webform_index_paginated", requirements={"page" : "\d+"})
     * @Cache(smaxage="10")
     */
    public function indexAction($page)
    {
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('AppBundle:WebForm');
        $queryBuilder = $repository->createQueryBuilder('p');
        $queryBuilder ->orderBy('p.publishedAt', 'DESC');

        if (!$user) {
            $queryBuilder->andWhere('p.status = :status')->setParameter('status', '1');
            $queryBuilder->andWhere('p.publishedAt <= :now')->setParameter('now', new \DateTime());
        } else if ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
            $queryBuilder->andWhere('p.status >= :status')->setParameter('status', '0');
        } else if ($user->hasRole('ROLE_EDITOR')) {
            $queryBuilder->andWhere('p.status >= :status')->setParameter('status', '1');
        } else {
            $queryBuilder->andWhere('p.status = :status')->setParameter('status', '1');
            $queryBuilder->andWhere('p.publishedAt <= :now')->setParameter('now', new \DateTime());
        }

        $query = $queryBuilder->getQuery();

        $paginator = $this->get('knp_paginator');
        $webforms = $paginator->paginate($query, $page, 25);
        $webforms->setUsedRoute('webform_index_paginated');

        return $this->render('webform/index.html.twig', array('webforms' => $webforms));
    }

    /**
     * @Route("/webforms/{slug}", name="webform")
     * @Method({"GET", "POST"})
     */
    public function webformShowAction(WebForm $webform, Request $request)
    {
        $formData = array();
        $user = $this->getUser();

        $formBuilder = $this->createFormBuilder($formData);

        foreach ($webform->getQuestions() as $question) {
            $choices=array();
            foreach ($question->getOptions() as $opt) {
                $choices[$opt->getContent()] = $opt->getId();
            }
            switch ($question->getType()) {
                case 'textbox':
                    $formBuilder->add('field_'.$question->getId(), TextareaType::class, array('label'=>$question->getContent(),'required'=>$question->getIsRequired()));
                    break;
                case 'textfield':
                    $formBuilder->add('field_'.$question->getId(), TextType::class, array('label'=>$question->getContent(),'required'=>$question->getIsRequired()));
                    break;
                case 'email':
                    $formBuilder->add('field_'.$question->getId(), EmailType::class, array('label'=>$question->getContent(),'required'=>$question->getIsRequired()));
                    break;
                case 'radio':
                    $formBuilder->add('field_'.$question->getId(), ChoiceType::class, array(
                        'label'=>$question->getContent(),
                        'required'=>$question->getIsRequired(),
                        'choices'  => $choices,
                        'choices_as_values' => true,
                        'multiple' => false,
                        'expanded' => true,
                        'empty_data'  => null,
                    ));
                    break;
                case 'dropdown':
                    $formBuilder->add('field_'.$question->getId(), ChoiceType::class, array(
                        'label'=>$question->getContent(),
                        'required'=>$question->getIsRequired(),
                        'choices'  => $choices,
                        'choices_as_values' => true,
                        'multiple' => false,
                        'expanded' => false,
                        'empty_data'  => null,
                    ));
                    break;
                case 'multicbox':
                    $formBuilder->add('field_'.$question->getId(), ChoiceType::class, array(
                        'label'=>$question->getContent(),
                        'required'=>$question->getIsRequired(),
                        'choices'  => $choices,
                        'choices_as_values' => true,
                        'multiple' => true,
                        'expanded' => true,
                        'empty_data'  => null,
                    ));
                    break;
            }
        }

        $formBuilder->add('submit', SubmitType::class);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $submission = new WebFormSubmission();
            $submission->setFormData(json_encode($data));
            $submission->setForm($webform);
            $submission->setUser($user);
            $submission->setIpAddress($request->getClientIp());
            $submission->setUserAgent($request->headers->get('user-agent'));
            $submission->setStatus(1);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($submission);
            $entityManager->flush();
            
            return $this->render('webform/webform_results.html.twig', array('webform' => $webform,'form'=>$form->createView()));
        }



        return $this->render('webform/webform_show.html.twig', array('webform' => $webform,'form'=>$form->createView()));
    }

}
