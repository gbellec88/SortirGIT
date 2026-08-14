<?php

namespace App\Close;

use App\Entity\Sortie;

class Close
{
    public function SortieClose(Sortie $sortie):bool
    {
        $dateDuJour = new \DateTimeImmutable();

        if ($sortie->getNbinscriptionMax()===$sortie->getParticipants()->count() ||
        $sortie->getDateLimiteInscription() < $dateDuJour)
        {
            return true;
        }

        return false;

    }


}
