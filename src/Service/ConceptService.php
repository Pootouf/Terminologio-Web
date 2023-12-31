<?php

namespace App\Service;

use App\Entity\ComponentName;
use App\Entity\Concept;
use App\Entity\DTO\ComponentTrad;
use App\Entity\Language;
use App\Entity\User;
use App\Repository\ComponentNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ConceptService
{
    private EntityManagerInterface $entityManager;

    //CONSTRUCTOR
    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    //REQUESTS
    public function getConceptsToShow(array $concepts, int $categoryNumber, int $languageNumber, int $userId): array
    {
        for ($i = sizeof($concepts) - 1; $i >= 0 ; $i--) {
            if ($this->isConceptNotInCategory($concepts[$i], $categoryNumber)
                or $this->isConceptNotTranslated($concepts[$i], $languageNumber)
                or !$this->isUserAuthorOfConcept($concepts[$i], $userId)) {
                array_splice($concepts, $i, 1);
            }
        }
        return $concepts;
    }

    //COMMANDS
    public function uploadConcept(?User $user, Concept $concept,
                                  string $newFilename): void
    {
        if($user != null) {
            $concept->setImage($newFilename);
            $concept->setAuthor($user);
            $concept->setIsValidated(false);
            $this->entityManager->persist($concept);
            $this->entityManager->flush();
        }
    }

    public function calculateComponentsWithTrad(Concept $concept, Language $language) : array {
        $componentsTrad = [];
        foreach ($concept->getComponents() as $component) {
            $componentNameGoodLanguage = null;
            foreach ($component->getComponentNames() as $componentName) {
                if ($componentName->getLanguage()->getName() == $language->getName()) {
                    $componentNameGoodLanguage = $componentName;
                }
            }

            $componentTrad = new ComponentTrad(
                $component->getId(),
                $component->getNumber(),
                $componentNameGoodLanguage == null ? "" : $componentNameGoodLanguage->getValue(),
                $component->getPositionX(),
                $component->getPositionY()
            );
            $componentsTrad[] = $componentTrad;
        }
        return $componentsTrad;
    }

    public function calculateComponentsWithDefaultTrad(Concept $concept) : array {
        return $this->calculateComponentsWithTrad($concept, $concept->getDefaultLanguage());
    }

    public function saveComponentNames(Concept $concept,
        Request $request,
        ComponentNameRepository $ComponentNameRepository,
        Language $language): void
    {
        $number = 0;
        $components = $concept->getComponents();
        while (($trad = $request->get('componentText'.$number)) != null) {
            $component_name = $ComponentNameRepository
                ->getComponentNameFromComponentAndLanguage($components[$number], $language);
            if($component_name == null) {
                $component_name = new ComponentName();
                $component_name->setLanguage($language);
                $component_name->setComponent($components[$number]);
            }
            $component_name->setValue($trad);
            $this->entityManager->persist($component_name);
            $number++;
        }
        $this->entityManager->flush();
    }



    private function isConceptNotInCategory(Concept $concept, int $categoryId): bool
    {
        return $concept->getCategory()->getId() <> $categoryId and $categoryId != -1;
    }

    private function isConceptNotTranslated(Concept $concept, int $languageId): bool
    {
        $firstComponent = $concept->getComponents()[0];
        if($firstComponent == null) {
            return $concept->getDefaultLanguage()->getId() <> $languageId and $languageId != -1;
        }
        $languagesOfConcept = array_map( fn(ComponentName $componentName): int => $componentName->getLanguage()->getId() ,
            $firstComponent->getComponentNames()->toArray());
        return ($concept->getDefaultLanguage()->getId() <> $languageId
            and !in_array($languageId, $languagesOfConcept) and $languageId != -1 );
    }

    private function isUserAuthorOfConcept(Concept $concept, int $userId): bool
    {
        return $userId == -1 or $concept->getAuthor()->getId() == $userId;
    }

}
