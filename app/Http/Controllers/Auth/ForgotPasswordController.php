<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Lang;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * The password token repository.
     *
     * @var \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->tokens = TokenRepositoryInterface::class;
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $credentials = $request->only('login');
        $user = $this->broker()->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        $file = getcwd() . '/../resources/twig/forgot-password.twig';
        $contaConteudo = "";
        if(file_exists($file)){
            $contaConteudo = file_get_contents($file);
        }

        $tituloApp = 'i - Educar';
        if (config('legacy.app.name')) {
            $tituloApp = config('legacy.app.name');
        }

        $token = $this->broker()->createToken($user);

        $urlReset = url(config('app.url').route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false));

        $contaConteudo = str_replace("{{ usuario }}",$user->getNameAttribute(),$contaConteudo);
        $contaConteudo = str_replace("{{ tituloApp }}",$tituloApp,$contaConteudo);
        $contaConteudo = str_replace("{{ redefinir }}",$urlReset,$contaConteudo);

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $response = "";
        try{
            $mail->isSMTP();
            $mail->CharSet = 'utf-8';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = config('mail.encryption');
            $mail->Host = config('mail.host');
            $mail->Port = config('mail.port');
            $mail->Username = config('mail.username');
            $mail->Password = config('mail.password');
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            $mail->Subject = Lang::getFromJson('Reset Password Notification');
            $mail->MsgHTML($contaConteudo);
            $mail->addAddress($user->getEmailAttribute() , $user->getNameAttribute());
            $mail->send();
            if($mail){
                $response = Password::RESET_LINK_SENT;
            }else{
                $response = "Erro ao enviar o e-mail!";
            }
        }catch(\Exception $e){
            $response = "Falha em enviar e-mail! " . $e->getMessage();
        }
        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
}
