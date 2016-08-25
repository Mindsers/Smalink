<?php

namespace Main\SiteBundle\Controller;

use Leg\GoogleChartsBundle\Charts\Gallery\Pie\ThreeDimensionsChart;
use Leg\GoogleChartsBundle\Charts\Gallery\Bar\VerticalGroupedChart;
use Leg\GoogleChartsBundle\Charts\Gallery\Bar\HorizontalGroupedChart;
use Leg\GoogleChartsBundle\Charts\Gallery\LineChart;
use Leg\GoogleChartsBundle\Charts\Gallery\PieChart;
use Leg\GoogleChartsBundle\Charts\Gallery\BarChart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Main\SiteBundle\Entity\Link;
use Main\SiteBundle\Entity\Click;

class DefaultController extends Controller
{

    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('main_site_dashboard');
        }

        return $this->render('MainSiteBundle:Default:index.html.twig');
    }

    public function dashboardAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $linkRepo = $this->getDoctrine()
                         ->getManager()
                         ->getRepository('MainSiteBundle:Link');
        $clickRepo = $this->getDoctrine()
                          ->getManager()
                          ->getRepository('MainSiteBundle:Click');

        /* Trois derniers liens */
        $listeliens = $linkRepo->findby(
            array('author' => $this->container->get('security.token_storage')->getToken()->getUser()),
            array('date' => 'DESC'),
            3
        );

        /* Stats resumée */
        $listeLiensComplete = $linkRepo->findBy(
            array('author' => $this->container->get('security.token_storage')->getToken()->getUser())
        );
        $nbLien = count($listeLiensComplete);

        $listeClicksComplete = $clickRepo->findBy(array('link' => $listeLiensComplete));
        $nbClick = count($listeClicksComplete);

        /* Nouveau lien */
        $lien = new Link();

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $lien->setAuthor($user);

        $formBuilder = $this->createFormBuilder($lien);

        $formBuilder
            ->add('name', TextType::class, array('attr'=> array('placeholder' => 'Etiquette')))
            ->add('lien_reel', TextType::class, array('attr'=> array('placeholder' => 'Lien à raccourcir')));

        $form = $formBuilder->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $lien->setLienSmall(randomLinkSufixe(6));
            $lien->setActivate(true);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($lien);
                $em->flush();

                return $this->redirectToRoute('main_site_dashboard');
            }
        }


        return $this->render('MainSiteBundle:Default:dashboard.html.twig', array(
        'form' => $form->createView(), 'liens' => $listeliens, 'nombreLiens' => $nbLien, 'nombreClick' => $nbClick));
    }

    public function newLinkAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $lien = new Link();

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $lien->setAuthor($user);

        $formBuilder = $this->createFormBuilder($lien);

        $formBuilder
            ->add('name', TextType::class, array('attr'=> array('placeholder' => 'Etiquette')))
            ->add('lien_reel', TextType::class, array('attr'=> array('placeholder' => 'Lien à raccourcir')));

        $form = $formBuilder->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $lien->setLienSmall(randomLinkSufixe(6));
            $lien->setActivate(true);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($lien);
                $em->flush();

                return $this->redirectToRoute('main_site_links');
            }
        }

        return $this->render('MainSiteBundle:Default:new.html.twig', array(
        'form' => $form->createView(),
        ));
    }

    public function allLinksAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $repository = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('MainSiteBundle:Link');

        $liens = $repository->findby(
            array('author' => $this->container->get('security.token_storage')->getToken()->getUser()),
            array('date' => 'DESC')
        );

        return $this->render('MainSiteBundle:Default:links.html.twig', array('liens' => $liens));
    }

    public function supprLinkAction(Link $lien)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($lien->getAuthor() == $this->container->get('security.token_storage')->getToken()->getUser()) {
            $this->getDoctrine()->getEntityManager()->remove($lien);
            $this->getDoctrine()->getEntityManager()->flush();
        } else {
            return $this->render('MainSiteBundle:Default:index.html.twig');
        }

        $this->getDoctrine()->getEntityManager()->remove($lien);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirect($this->container->get('request_stack')->getCurrentRequest()->headers->get('referer'));
    }

    public function activeLinkAction(Link $lien)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        switch ($lien->getActivate()) {
            case false:
                if ($lien->getAuthor() == $this->container->get('security.token_storage')->getToken()->getUser()) {
                    $lien->setActivate(true);
                    $this->getDoctrine()->getEntityManager()->flush();
                }
                break;
            case true:
                if ($lien->getAuthor() == $this->container->get('security.token_storage')->getToken()->getUser()) {
                    $lien->setActivate(false);
                    $this->getDoctrine()->getEntityManager()->flush();
                }
                break;

            default:
                if ($lien->getAuthor() == $this->container->get('security.token_storage')->getToken()->getUser()) {
                    $lien->setActivate(true);
                    $this->getDoctrine()->getEntityManager()->flush();
                }
                break;
        }

        return $this->redirect($this->container->get('request_stack')->getCurrentRequest()->headers->get('referer'));
    }

    public function statsAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $linkRepo = $this->getDoctrine()
                         ->getManager()
                         ->getRepository('MainSiteBundle:Link');
        $clickRepo = $this->getDoctrine()
                          ->getManager()
                          ->getRepository('MainSiteBundle:Click');

        /* Stats resumée */
        $listeLiensComplete = $linkRepo->findBy(array(
            'author' => $this->container->get('security.token_storage')->getToken()->getUser()
        ));
        $nbLien = count($listeLiensComplete);

        $listeClicksComplete = $clickRepo->findBy(array('link' => $listeLiensComplete));
        $nbClick = count($listeClicksComplete);

        // Graphe Click / Date
        $annee = date('Y');
        $nbClickCurrentYear = array();
        $nbClickPrecedentYear = array();

        $janv = 0;
        $fevr = 0;
        $mars = 0;
        $avri = 0;
        $mai = 0;
        $juin = 0;
        $juil = 0;
        $aout = 0;
        $sept = 0;
        $octo = 0;
        $nove = 0;
        $dece = 0;

        $janv_ = 0;
        $fevr_ = 0;
        $mars_ = 0;
        $avri_ = 0;
        $mai_ = 0;
        $juin_ = 0;
        $juil_ = 0;
        $aout_ = 0;
        $sept_ = 0;
        $octo_ = 0;
        $nove_ = 0;
        $dece_ = 0;

        foreach ($listeClicksComplete as $key => $clicks) {
            if ($clicks->getDate()->format('Y') == $annee) {
                switch ($clicks->getDate()->format('m')) {
                    case '01':
                        $janv = $janv + 1;
                        break;
                    case '02':
                        $fevr = $fevr + 1;
                        break;
                    case '03':
                        $mars = $mars + 1;
                        break;
                    case '04':
                        $avri = $avri + 1;
                        break;
                    case '05':
                        $mai = $mai + 1;
                        break;
                    case '06':
                        $juin = $juin + 1;
                        break;
                    case '07':
                        $juil = $juil + 1;
                        break;
                    case '08':
                        $aout = $aout + 1;
                        break;
                    case '09':
                        $sept = $sept + 1;
                        break;
                    case '10':
                        $octo = $octo + 1;
                        break;
                    case '11':
                        $nove = $nove + 1;
                        break;
                    case '12':
                        $dece = $dece + 1;
                        break;

                    default:
                        # code...
                        break;
                }
            } elseif ($clicks->getDate()->format('Y') == ($annee-1)) {
                switch ($clicks->getDate()->format('m')) {
                    case '01':
                        $janv_ = $janv_ + 1;
                        break;
                    case '02':
                        $fevr_ = $fevr_ + 1;
                        break;
                    case '03':
                        $mars_ = $mars_ + 1;
                        break;
                    case '04':
                        $avri_ = $avri_ + 1;
                        break;
                    case '05':
                        $mai_ = $mai_ + 1;
                        break;
                    case '06':
                        $juin_ = $juin_ + 1;
                        break;
                    case '07':
                        $juil_ = $juil_ + 1;
                        break;
                    case '08':
                        $aout_ = $aout_ + 1;
                        break;
                    case '09':
                        $sept_ = $sept_ + 1;
                        break;
                    case '10':
                        $octo_ = $octo_ + 1;
                        break;
                    case '11':
                        $nove_ = $nove_ + 1;
                        break;
                    case '12':
                        $dece_ = $dece_ + 1;
                        break;

                    default:
                        break;
                }
            }
        }

        $nbClickCurrentYear = array(
            $janv,
            $fevr,
            $mars,
            $avri,
            $mai,
            $juin,
            $juil,
            $aout,
            $sept,
            $octo,
            $nove,
            $dece
        );
        $nbClickPrecedentYear = array(
            $janv_,
            $fevr_,
            $mars_,
            $avri_,
            $mai_,
            $juin_,
            $juil_,
            $aout_,
            $sept_,
            $octo_,
            $nove_,
            $dece_
        );

        // Graphe Click / Référant
        $listeRefCurrentYear = $clickRepo->findDistByYear('referrer', $annee, 3);
        $listeRefPrecedentYear = $clickRepo->findDistByYear('referrer', $annee-1, 3);

        // Graphe Click / Référant
        $listeCountryCurrentYear = $clickRepo->findDistByYear('country', $annee, 3);
        $listeCountryPrecedentYear = $clickRepo->findDistByYear('country', $annee-1, 3);


        return $this->render(
            'MainSiteBundle:Default:stats.html.twig',
            array(
                'nombreLiens' => $nbLien,
                'nombreClick' => $nbClick,
                'clickThisYear' => $nbClickCurrentYear,
                'clickPrecedentYear' => $nbClickPrecedentYear,
                'refThisYear' => $listeRefCurrentYear,
                'refPrecedentYear' => $listeRefPrecedentYear,
                'countryThisYear' => $listeCountryCurrentYear,
                'countryPrecedentYear' => $listeCountryPrecedentYear
            )
        );
    }

    public function linkInfoAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $linkRepo = $this->getDoctrine()
                         ->getManager()
                         ->getRepository('MainSiteBundle:Link');
        $clickRepo = $this->getDoctrine()
                          ->getManager()
                          ->getRepository('MainSiteBundle:Click');

        // Lien
        $link = $linkRepo->find($id);

        // Stats resumée
        $listeLiensComplete = $linkRepo->findBy(array(
            'author' => $this->container->get('security.token_storage')->getToken()->getUser()
        ));
        $nbLien = count($listeLiensComplete);

        $listeClicksComplete = $clickRepo->findBy(array('link' => $listeLiensComplete));
        $nbClick = count($listeClicksComplete);

        // Graphe Click / Date
        $annee = date('Y');

        $listClick = $clickRepo->clickRecentByLink($link);

        $date_array = array();
        $datetmp = new \Datetime(date('d-m-Y'));
        $datetmp->sub(new \DateInterval('P14D'));

        if (end($listClick)) {
            $endDate = end($listClick)->getDate();
        } else {
            $endDate = new \Datetime('now');
        }

        foreach ($listClick as $key => $click) { // detection de la plus ancienne date de click
            if ($datetmp > $click->getDate()) {
                $datetmp = $click->getDate();
            }
        }

        foreach ($listClick as $key => $click) { // detection de la plus recente date de click
            if ($endDate < $click->getDate()) {
                $endDate = $click->getDate();
            }
        }

        $endDate->add(new \DateInterval('P1D'));

        while ($datetmp->format('Y-m-d') != $endDate->format('Y-m-d')) {
            $date_array[$datetmp->format('Y-m-d')]['date'] = $datetmp->format('Y-m-d');
            $date_array[$datetmp->format('Y-m-d')]['click'] = 0;
            $datetmp->add(new \DateInterval('P1D'));
        }

        $endDate->sub(new \DateInterval('P1D'));

        foreach ($listClick as $key => $click) {
            $dateFormated = $click->getDate()->format('Y-m-d');
            if (isset($date_array[$dateFormated])) {
                $date_array[$dateFormated]['click'] = $date_array[$dateFormated]['click'] + 1;
            }
        }

        // Graphe Click / Référant
        $listeRef = $clickRepo->findDistRefByLink($id, 7);

        // Graphe Click / Référant
        $listeCountry = $clickRepo->findDistCountryByLink($id, 7);

        return $this->render(
            'MainSiteBundle:Default:statsLink.html.twig',
            array(
                'lien'=>$link,
                'clickList' => $date_array,
                'refList' => $listeRef,
                'countryList' => $listeCountry
            )
        );
    }

    public function shortenerAction($short)
    {
        $repository = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('MainSiteBundle:Link');

        $lien = $repository->findOneBy(array('lien_small' => $short));

        if (is_object($lien)) {
            if ($lien->getActivate()) {
                $countryUser = json_decode(file_get_contents(
                    "http://ipinfo.io/" . $this->container->get('request_stack')->getCurrentRequest()->getClientIp()
                ));
                $countryUser = $countryUser->country;
                $referrerUrl = parse_url(
                    $this->get('request_stack')->getCurrentRequest()->headers->get('referer'),
                    PHP_URL_HOST
                );

                if ($referrerUrl == null) {
                    $referrerUrl = "Direct";
                }

                if ($countryUser == "XX") {
                    $countryUser = "Inconnu";
                }

                $click = new Click;

                $click->setLink($lien)
                      ->setCountry($countryUser)
                      ->setReferrer($referrerUrl);

                $this->getDoctrine()->getEntityManager()->persist($click);
                $this->getDoctrine()->getEntityManager()->flush();

                return $this->redirect($lien->getLienReel());
            } else {
                return $this->render('MainSiteBundle:Default:falselink.html.twig');
            }
        } else {
            return $this->render('MainSiteBundle:Default:nolink.html.twig');
        }
    }
}

function randomLinkSufixe($length)
{
    $linkSufixe = "";
    $alpha = "aAbBcCdDeEfFgGhHiIjJkKlLmMnNpPqQrRsStTuUvVwWxXyYzZ";
    srand((double)microtime()*1000000);

    for ($i=0; $i<$length; $i++) {
        $linkSufixe .= $alpha[rand()%strlen($alpha)];
    }

    return $linkSufixe;
}
