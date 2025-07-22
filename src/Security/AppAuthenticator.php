<?php

namespace App\Security;

// Chargement des composants et des fichiers nécessaires pour la construction de l'authentificateur
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

// La classe AppAuthenticator hérite des priopriétés d'AbstractLoginFormAuthenticator et gère la connexion de l'utilisateur
class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    // Après la connexion réussie TargetPathTrait permet la redirection vers la page demandée
    use TargetPathTrait;

    // Le constable LOGIN_ROUTE définie la route du formulaire de connexion
    public const LOGIN_ROUTE = 'app_login';

    // Definition du $urlGenerator pour generer le lien dans l'Url
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    // La fonction permet la récupération des données du formulaire avec un token CSRF et crée un passeport pour l'authentification de l'utilisateur
    public function authenticate(Request $request): Passport
    {
        // Récupération de l'email
        $email = $request->getPayload()->getString('email');
        // En cas d'échec de connexion l'email sera récupéré et affiché dans l'input, pour faciliter le processus.
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Création d'un passeport rempli avec les données de connexion y compris le token et l'option "se souvenir de moi"
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    // Fonction qui gère la rédiréction vers la page 'feed' après une connexion réussie
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_feed'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    // La récuperération de l'URL du formulaire de connexion en cas de problème de connexion
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
