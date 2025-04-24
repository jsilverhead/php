Можно не подгружать сущность целиком, если нужно просто обнаружить что она есть и отдать boolean.
```php
function hasUserAccessToOffice(Office $office, User $user): bool {
$result = (bool) $this->createQueryBuilder('o')
->select('1')
->join('o.users', 'u')
->where('u.id = :userId')
->setParameter('userId', $user->id, UuidType::NAME)
->andWhere('o.id = :officeId')
->setParameter('officeId', $office->id, UuidType::NAME)
->setMaxResults(1)
->getQuery()
->getOneOrNullResult();

return $result;
}
```