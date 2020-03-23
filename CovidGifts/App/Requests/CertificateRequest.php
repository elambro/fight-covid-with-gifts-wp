<?php namespace CovidGifts\App\Requests;

use CovidGifts\App\Abstracts\Request;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Contracts\Request as RequestInterface;
use CovidGifts\App\Exceptions\PaymentException;

class CertificateRequest extends Request implements RequestInterface
{

    private $action;

    public function __construct($action)
    {
        $this->action = $action;
        parent::__construct();
    }

    public function handle()
    {
        cvdapp()->csrf()->check();
        
        $cert = $this->getCertificate();

        switch($this->action)
        {
            case 'paid':
                $cert->markPaid();
                break;

            case 'unpaid':
                $cert->markUnpaid();
                break;

                
            case 'use':
                if ($cert->used_at || !$cert->accepted_at || $cert->cancelled_at || !$cert->paid_at) {
                    throw new PaymentException('errors.cannot-use');
                }
                $cert->markUsed();
                break;

            case 'unused':
                $cert->markUnused();
                break;

            case 'accept':
                if (!$cert->paid_at) {
                    throw new PaymentException('errors.cannot-accept');
                }
                $cert->markAccepted();
                break;


            case 'cancel':
                if ($cert->used_at ) {
                    throw new PaymentException('errors.cannot-cancel');
                }
                $cert->markCancelled();
                break;

            case 'delete':
                if ($cert->used_at || $cert->accepted_at || $cert->paid_at || !$cert->cancelled_at) {
                    throw new PaymentException('errors.cannot-delete');
                }
                $cert->delete();
                return ['deleted' => true];

        }

        return ['attributes' => $cert->getAttributes()];
    }

    private function getCertificate()
    {
        $c = cvdapp()->container->getResolver(GiftCertificate::class);
        $cert = $c::find($this->get('id'));
        if (!$cert) {
            throw new NotFoundException();   
        }
        return $cert;
    }

    public function build()
    {
        return [
            'id' => $this->postedInt('id')
        ];
    }

    public function validate()
    {
        return true;
    }
}