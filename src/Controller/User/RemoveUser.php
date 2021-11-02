<?php


namespace App\Controller\User;


use App\Entity\User\User;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RemoveUser
{
public function __invoke(User $data)
{
    if ($data->hasRole('ROLE_ADMIN')) {
        throw new UnprocessableEntityHttpException('Нельзя удалить администратора');
    }
    return $data;
}
}