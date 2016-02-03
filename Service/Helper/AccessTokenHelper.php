<?php
/**
 * Date: 22.12.15
 *
 * @author Portey Vasil <portey@gmail.com>
 */

namespace Youshido\TokenAuthenticatorBundle\Service\Helper;


use Symfony\Component\DependencyInjection\ContainerAware;
use Youshido\DoctrineExtensionBundle\Traits\Service\ServiceHelperTrait;
use Youshido\TokenAuthenticatorBundle\Entity\AccessToken;

class AccessTokenHelper extends ContainerAware
{
    use ServiceHelperTrait;

    /** @var int */
    protected $tokenLifetime;

    /**
     * @param AccessToken $token
     *
     * @return bool
     */
    public function checkExpires(AccessToken $token)
    {
        if (time() < $token->getCreatedAt()->getTimestamp() + $this->tokenLifetime) {
            $token->setStatus(AccessToken::STATUS_EXPIRED);

            $this->persist($token);
            $this->flush();

            return false;
        }

        return true;
    }

    /**
     * @param $accessToken
     *
     * @return null|AccessToken
     */
    public function find($accessToken)
    {
        return $this->getDoctrine()->getRepository('TokenAuthenticatorBundle:AccessToken')
            ->findOneBy(['value' => $accessToken]);
    }

    /**
     * @param $modelId  int
     * @param $withSave bool
     *
     * @return AccessToken
     */
    public function generateToken($modelId, $withSave = true)
    {
        $token = new AccessToken();

        $token
            ->setModelId($modelId)
            ->setValue(base64_encode(md5(time() . $modelId)));

        if ($withSave) {
            $this->persist($token);
            $this->flush();
        }

        return $token;
    }

    /**
     * @param $id
     * @return AccessToken
     */
    public function findTokenByModelId($id)
    {
        return $this->getDoctrine()->getRepository('TokenAuthenticatorBundle:AccessToken')->findOneBy(['modelId' => $id]);
    }

    public function transformToArray(AccessToken $token)
    {
        return [
            'accessToken' => $token->getValue(),
            'expiresAt'   => date('Y-m-d H:i:s', $token->getCreatedAt()->getTimestamp() + $this->tokenLifetime)
        ];
    }

    /**
     * @return int
     */
    public function getTokenLifetime()
    {
        return $this->tokenLifetime;
    }

    /**
     * @param int $tokenLifetime
     */
    public function setTokenLifetime($tokenLifetime)
    {
        $this->tokenLifetime = $tokenLifetime;
    }
}
