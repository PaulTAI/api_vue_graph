<?php

namespace App\Manager;

use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Service\GroupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RoleManager
{
    protected $RoleRepository;
    protected $entityManager;
    private $groupService;

    public function __construct(EntityManagerInterface $entityManager, RoleRepository $RoleRepository, GroupService $groupService)
    {
        $this->entityManager = $entityManager;
        $this->RoleRepository = $RoleRepository;
        $this->groupService = $groupService;
    }

    /**
     * create a role
     *
     * @param Role $role
     * @return void
     */
    public function createRole(Role $role)
    {
        $name = $role->getName();
        $rights = $role->getRights();
        $users = $role->getUsers();
        $group = $role->getRoleGroup();

        $userRights = $this->groupService->getUserRightsInGroup($group);

        // 3 => id du droit : creer un role dans le groupe
        if(in_array("owner", $userRights) || in_array(3, $userRights)){
            
            $this->RoleRepository->insertRole($name, $rights, $users, $group);

            return [
                "message" => "Le rôle a été créé avec succès",
                "role" => $role
            ];
        }

        return [
            "message" => "Vous n'avez pas les droits pour créer un rôle",
            "rights" => $userRights
        ];
    }

    /**
     * delete a role
     *
     * @return void
     */
    public function deleteRole(Role $role){
        $userRights = $this->groupService->getUserRightsInGroup($role->getRoleGroup());

        // 3 => id du droit : supprimer un role dans le groupe
        if(in_array("owner", $userRights) || in_array(4, $userRights)){
            
            $this->RoleRepository->deleteRole($role);

            return [
                "message" => "Le rôle a été supprimé avec succès",
                "role" => $role
            ];
        }

        return [
            "message" => "Vous n'avez pas les droits pour supprimer un rôle"
        ];
    }
}
