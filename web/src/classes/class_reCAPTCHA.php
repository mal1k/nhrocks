<?

    /* ==================================================================*\
      ######################################################################
      #                                                                    #
      # Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
      #                                                                    #
      # This file may not be redistributed in whole or part.               #
      # eDirectory is licensed on a per-domain basis.                      #
      #                                                                    #
      # ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
      #                                                                    #
      # http://www.edirectory.com | http://www.edirectory.com/license.html #
      ######################################################################
      \*================================================================== */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /classes/class_reCAPTCHA.php
    # ----------------------------------------------------------------------------------------------------

    /* We'll put this here just to force session start.
     * Tip: @ is an error supressor operator */
    use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;

    @session_start();

    class reCAPTCHA
    {
        /**
         * Keeps the google recaptcha settings to avoid loading data from the DB
         * @var array
         */
        public $settings;

        /**
         * Is it the old or new captcha?
         * @var boolean
         */
        public $isNew;

        /**
         * What language will reCAPTCHA use?
         * @var string
         */
        public $language;

        private $container;

        /**
         * This translates eDirectory language codes into reCAPTCHA language codes.
         * @var string[]
         */
        public static $languageLibrary = array(
            "en_us" => "en",
            "pt_br" => "pt-BR",
            "es_es" => "es",
            "tr_tr" => "tr",
            "ge_ge" => "de",
            "fr_fr" => "fr",
            "it_it" => "it",
        );

        public function __construct()
        {
            setting_get('google_recaptcha_status', $google_recaptcha_status);
            setting_get('google_recaptcha_sitekey', $google_recaptcha_sitekey);
            setting_get('google_recaptcha_secretkey', $google_recaptcha_secretkey);

            $this->settings = [
                'recaptcha_status' => $google_recaptcha_status,
                'recaptcha_sitekey' => $google_recaptcha_sitekey,
                'recaptcha_secretkey' => $google_recaptcha_secretkey
            ];

            $this->isNew    = $this->settings['recaptcha_status'] == "on";
            $this->language = self::$languageLibrary[ EDIR_LANGUAGE ];
            $this->container = SymfonyCore::getContainer();
        }

        /**
         * This function will render the right recaptcha form field according to user's settings
         */
        public function render()
        {
            $captcha = '';

            if( $this->isNew )
            {
                /* Google's reCAPTCHA library. The parameters indicate we'll defer it's loading and call onloadCallback
                 * when it finally loads. The reason for this is to avoid putting scripts in the page's HEAD, which is
                 * to the present date, a bad programming practice. */
                JavaScriptHandler::registerLone("", "src=\"//www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl={$this->language}\" async defer");
                JavaScriptHandler::registerLoose("
                    var onloadCallback = function() {
                        grecaptcha.render(\"reCaptchaContainer\", {
                            \"sitekey\" : \"{$this->settings['recaptcha_sitekey']}\"
                        });
                    };
                    ");

                $captcha .= "<div id='reCaptchaContainer'></div>";
            }
            else
            {
                $options = [
                    'reload' => true,
                    'as_url' => true,
                    'label'  => false
                ];

                $form = $this->container->get('form.factory')->createBuilder();

                $form->add('accountCaptcha', CaptchaType::class, $options);

                $form->getForm()->createView();

                $captcha .= '<div class="form-captcha">
                        <img src="/generate-captcha/gcb_accountCaptcha" alt="captcha" title="captcha" width="150" height="50"/>
                        <input type="text" value="" name="accountCaptcha" class="input" />
                    </div>
                    ';
            }

            return $captcha;
        }

        /**
         * This will validate recaptcha to make sure the user has entered the right code.
         * @return boolean
         */
        public function validate()
        {
            $return = false;

            if( $this->isNew )
            {
                $request = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$this->settings['recaptcha_secretkey']}&response={$_POST['g-recaptcha-response']}&remoteip={$_SERVER['REMOTE_ADDR']}");

                if( $response = json_decode($request) )
                {
                    $return = $response->success;
                }
            } else if($_POST['accountCaptcha'] === $_SESSION['_sf2_attributes']['gcb_accountCaptcha']['phrase']) {
                $return = true;
            } elseif ($_POST['form']['registerCaptcha'] === $_SESSION['_sf2_attributes']['gcb_registerCaptcha']['phrase']) {
                $return = true;
            }

            return (bool)$return;
        }
    }
