Introduction
============

This Bundle enables integration of mozillas PERSONA verification API. It also
provides a Symfony2 authentication provider so that users can login to a Symfony2
via persona's Remote Verification API. Furthermore via custom user provider support
the persona login can also be integrated with some other data sources like the
database based solution provided by the famous FOSUserBundle.

Note that logging in a user requires 3 steps:

  1. the user must have a valid personal authentification account
  2. you have to trigger the symfony2 login
  3. Add the persona login button twig helper inside your login.html.twig template

further information about persona verifaction api can be found on
https://developer.mozilla.org/de/docs/persona

Please also refer to the official documentation of the SecurityBundle, especially
for details on the configuration:
http://symfony.com/doc/current/book/security.html

Prerequisites
============

This version requires Symfony 2.1


Installation
============

  1. Add the following lines in your composer.json:
```
{
    "require": {
        "bitgrave/persona-bundle": "dev-master"
    }
}
```
  2. Run the composer to download the bundle
```
    $ php composer.phar update bitgrave/persona-bundle
```

  3. Add this bundle to your application's kernel:
```
      // app/ApplicationKernel.php
      public function registerBundles()
      {
          return array(
              // ...
              new BG\PersonaBundle\BGPersonaBundle(),
              // ...
          );
      }
```
  4. Configure the `persona` service in your config:
```
  # application/config/config.yml
  bg_persona:
        verifier_url: 'https://verifier.login.persona.org/verify'
        audience_url: %webapp_url_ssl%:443
```

  4.1. If you want to use `security component` add this configuration:
```
  # application/config/config.yml
  bg_persona:
        default_target_path: /
        provider: my_persona.persona_provider
        login_path: /login
        check_path: /persona_login_check
```

  4.2. define a custom user provider class and use it as provider or define login path
```
  # application/config/config.yml
  security:
        my_persona.persona_provider:
            id: my_persona.persona.user

  firewalls:
        main:
            bg_persona:
                default_target_path: /
                provider: my_persona.persona_provider
                login_path: /login
                check_path: /persona_login_check
```

  5. add routing for persona logincheck handler
```
  # application/config/routing.yml
  _persona_security_check:
        pattern:   /persona_login_check
```

  6. add a persona host ident configuration inside your parameters.yml
  ```
  # application/config/parameters.yml
  webapp_url:         http://www.example.com
  webapp_url_ssl:     https://www.example.com
  ```


Include the persona login button in your templates
--------------------------------------------------
add the following code in your login template (thats a twig sample):
```
<!-- inside your login twig template -->
{{ persona_login_button() }}
```

Example Custom User Provider using the BG\PersonaBundle
-------------------------------------------------------
This requires adding a service for the custom user provider which is then set
to the provider id in the "provider" section in the config.yml:
```
my_persona.persona.user:
    class: Nmq\UserBundle\Security\User\Provider\PersonaProvider
    arguments:
        persona: "@bg_persona.service"
        userManager: "@fos_user.user_manager"
        validator: "@validator"
        session: "@session"
        container: "@service_container"
```

```
<?php

namespace Acme\MyBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use BG\PersonaBundle\Services\BasePersona;
use Symfony\Component\HttpFoundation\Session;

class PersonaProvider implements UserProviderInterface
{
    protected $userManager;
    protected $validator;
    protected $session;
    protected $persona;

    public function __construct(BasePersona $persona, $userManager, $validator, $session)
    {
        $this->persona = $persona;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->session = $session;
    }

    // main auth entry point, load user on exist, create user on non-existance
    public function loadUserByUsername($p_persona_email)
    {
        $user = $this->findUserByPersonaId($p_persona_email);
        $t_persona_email = $this->session->get('persona_email');
        $t_persona_status = $this->session->get('persona_status');
        $t_persona_expires = $this->session->get('persona_expires');

        // compare persona expires microtimestamp with current one ...
        if (($t_persona_status==='okay')&&($t_persona_expires>=round((microtime(true) * 1000))))
        {
            if (empty($user))
            {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPassword('');
            }

            $user->setPersonaId($t_persona_email);
            $user->setPersonaLastStatus($t_persona_status);
            $user->addRole('ROLE_PERSONA_USER');
            $user->setPersonaExpires($t_persona_expires);
            $this->userManager->updateUser($user);

            // kill old persona session stack
            $this->session->set('persona_email', null);
            $this->session->set('persona_expires', null);
            $this->session->set('persona_status', null);
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on persona');
        }

        return $user;
    }

    public function findUserByPersonaId($personaEmail)
    {
        return $this->userManager->findUserBy(array('persona_email' => $personaEmail));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getPersonaEmail()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getPersonaEmail());
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }
```

Finally one also needs to add a getPersonaId() and setPersonaId() method to the User model.
take note that field placements firstname and lastname not realy neccesary for our persona
bundle implementation. The following example using the Doctrine ORM + FOSUserBundle:
```
<?php
namespace Acme\MyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;

/**
* Nmq\UserBundle\Entity\User
* @ORM\Table(name="nmq_user")
* @ORM\Entity(repositoryClass="Nmq\UserBundle\Entity\UserRepository")
* @Gedmo\SoftDeleteable(fieldName="deletedAt")
*
*/
class User extends BaseUser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    // ////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * @var string $firstname
     *
     * @ORM\Column(name="firstname", type="string", length=80, nullable=true)
     */
    protected $firstname;


    /**
     * @var string $lastname
     *
     * @ORM\Column(name="lastname", type="string", length=80, nullable=true)
     */
    protected $lastname;

    /**
     * @param string $persona_lastStatus
     */
    public function setPersonaLastStatus($persona_lastStatus)
    {
        $this->persona_lastStatus = $persona_lastStatus;
    }

    /**
     * @return string
     */
    public function getPersonaLastStatus()
    {
        return $this->persona_lastStatus;
    }

    /**
     * @param string $persona_lastFailReason
     */
    public function setPersonaLastFailReason($persona_lastFailReason)
    {
        $this->persona_lastFailReason = $persona_lastFailReason;
    }

    /**
     * @return string
     */
    public function getPersonaLastFailReason()
    {
        return $this->persona_lastFailReason;
    }

    public function setPersonaExpires($persona_expires)
    {
        $this->persona_expires = $persona_expires;
    }

    public function getPersonaExpires()
    {
        return $this->persona_expires;
    }

    /**
     * @param string $persona_email
     */
    public function setPersonaEmail($persona_email)
    {
        $this->persona_email = $persona_email;
    }

    /**
     * @return string
     */
    public function getPersonaEmail()
    {
        return $this->persona_email;
    }


    /**
     * @var string $persona_email
     *
     * @ORM\Column(name="persona_email", type="string", length=255, nullable=true)
     */
    protected $persona_email;


    /**
     * @ORM\Column(name="persona_expires", type="integer", nullable=true)
     */
    protected $persona_expires;

    /**
     * @var string persona_lastStatus
     * @ORM\Column(name="persona_last_status", type="string", length=8, nullable=true)
     */
    protected $persona_lastStatus;


    /**
     * @var string persona_lastFailReason
     * @ORM\Column(name="persona_last_fail_reason", type="string", nullable=true)
     */
    protected $persona_lastFailReason;



    // ////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Set lastname value for user
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname value for user
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname value for user
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname value for user
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }


    // ////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /***
     * Set users personaId
     *
     * @param $persona_email
     */
    public function setPersonaId($persona_email)
    {
        $this->email = $persona_email;
        $this->emailCanonical = $this->email;
        $this->setUsername($this->email);
        $this->setPersonaEmail($this->email);
        $this->salt = '';
    }


    /**
     * Get the full name of the user (first + last name)
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastname();
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////////////


    public function __construct()
    {
        parent::__construct();

    }
}
```