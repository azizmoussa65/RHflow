<?php
namespace App\DataFixtures;

use App\Entity\Conge;
use App\Entity\Contrat;
use App\Entity\DossierAdmin;
use App\Entity\Evaluation;
use App\Entity\Projet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Loads demo data into the database.
 * Run with: php bin/console doctrine:fixtures:load
 *
 * Demo accounts:
 *   manager@hrflow.tn / password   (MANAGER)
 *   rh@hrflow.tn      / password   (RH)
 *   employe@hrflow.tn / password   (EMPLOYE)
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ── 1. USERS ──────────────────────────────────────────────
        $manager_user = $this->makeUser($manager, 'Ahmed',   'Mansouri', 'manager@hrflow.tn', ['ROLE_MANAGER'], 'Direction Générale', 'Manager Général');
        $rh_user      = $this->makeUser($manager, 'Omar',    'Trabelsi', 'rh@hrflow.tn',      ['ROLE_RH'],      'Ressources Humaines','Responsable RH');
        $emp1         = $this->makeUser($manager, 'Karima',  'Belhadj',  'k.belhadj@hrflow.tn',['ROLE_EMPLOYE'],'Développement',    'Développeuse Frontend');
        $emp2         = $this->makeUser($manager, 'Yassine', 'Aouat',    'y.aouat@hrflow.tn',  ['ROLE_EMPLOYE'],'Marketing',         'Chef de projet digital');
        $emp3         = $this->makeUser($manager, 'Sana',    'Mrad',     's.mrad@hrflow.tn',   ['ROLE_EMPLOYE'],'Design',            'UI/UX Designer');
        $emp4         = $this->makeUser($manager, 'Mehdi',   'Khelifi',  'm.khelifi@hrflow.tn',['ROLE_EMPLOYE'],'Finance',           'Comptable Senior');
        $emp5         = $this->makeUser($manager, 'Lina',    'Bouzid',   'l.bouzid@hrflow.tn', ['ROLE_EMPLOYE'],'Développement',    'Développeuse Backend');
        $emp_generic  = $this->makeUser($manager, 'Employé', 'Demo',     'employe@hrflow.tn',  ['ROLE_EMPLOYE'],'RH',                'Agent RH');

        [$manager_user,$rh_user,$emp1,$emp2,$emp3,$emp4,$emp5,$emp_generic] = array_map(function($u) use ($manager) {
            $manager->persist($u); return $u;
        }, [$manager_user,$rh_user,$emp1,$emp2,$emp3,$emp4,$emp5,$emp_generic]);

        // ── 2. CONTRATS ──────────────────────────────────────────
        $contrats = [
            [$emp1,'CDI', '2024-01-01',null,  3200,'Actif'],
            [$emp2,'CDI', '2023-03-01',null,  2900,'Actif'],
            [$emp3,'CDD', '2024-06-01','2025-05-31',2400,'Actif'],
            [$emp4,'CDI', '2021-09-01',null,  4100,'Actif'],
            [$emp5,'Stage','2025-02-01','2025-07-31',600,'Actif'],
        ];
        foreach ($contrats as [$emp,$type,$start,$end,$sal,$statut]) {
            $c = new Contrat();
            $c->setEmploye($emp)->setType($type)
              ->setDateDebut(new \DateTime($start))
              ->setDateFin($end ? new \DateTime($end) : null)
              ->setSalaire($sal)->setStatut($statut);
            $manager->persist($c);
        }

        // ── 3. CONGÉS ───────────────────────────────────────────
        $congeData = [
            [$emp2,'Annuel','2025-03-15','2025-03-20',5,'Vacances','EN_ATTENTE'],
            [$emp1,'Maladie','2025-03-22','2025-03-24',3,'Médical','EN_ATTENTE'],
            [$emp3,'Annuel','2025-04-01','2025-04-11',10,'Voyage','APPROUVE'],
            [$emp4,'Exceptionnel','2025-03-10','2025-03-12',3,'Mariage','REFUSE'],
        ];
        foreach ($congeData as [$emp,$type,$d1,$d2,$nb,$motif,$statut]) {
            $c = new Conge();
            $c->setEmploye($emp)->setType($type)
              ->setDateDebut(new \DateTime($d1))->setDateFin(new \DateTime($d2))
              ->setNbJours($nb)->setMotif($motif)->setStatut($statut);
            $manager->persist($c);
        }

        // ── 4. PROJETS ──────────────────────────────────────────
        $proj1 = new Projet();
        $proj1->setNom('ERP v3.0')->setCategorie('Développement')->setStatut('En cours')
              ->setDescription('Refonte complète du système ERP interne.')->setAvancement(68)
              ->setDeadline(new \DateTime('2025-04-30'))->setCouleur('#3b82f6')
              ->addMembre($emp1)->addMembre($emp5)->addMembre($emp2);
        $manager->persist($proj1);

        $proj2 = new Projet();
        $proj2->setNom('App Mobile RH')->setCategorie('Mobile / Design')->setStatut('En pause')
              ->setDescription('Application mobile pour les employés.')->setAvancement(34)
              ->setDeadline(new \DateTime('2025-06-15'))->setCouleur('#f59e0b')
              ->addMembre($emp3)->addMembre($emp2);
        $manager->persist($proj2);

        // ── 5. ÉVALUATIONS ──────────────────────────────────────
        $evalData = [
            [$emp1,'Q1 2025',4.8,4.5,4.4],
            [$emp2,'Q4 2024',3.5,4.2,3.5],
            [$emp3,'Q1 2025',5.0,4.5,4.3],
        ];
        foreach ($evalData as [$emp,$per,$comp,$team,$init]) {
            $ev = new Evaluation();
            $ev->setEmploye($emp)->setPeriode($per)
               ->setCompetences($comp)->setTeamwork($team)->setInitiative($init);
            $ev->computeGlobalNote();
            $manager->persist($ev);
        }

        // ── 6. DOSSIERS ──────────────────────────────────────────
        $dossierData = [
            [$emp1,'Licence Informatique','Diplôme','Validé'],
            [$emp2,'Attestation travail 2024','Attestation','En attente'],
            [$emp5,'Certif. AWS Solutions','Certificat','Validé'],
            [$emp4,'Master Finance','Diplôme','Validé'],
        ];
        foreach ($dossierData as [$emp,$titre,$type,$statut]) {
            $d = new DossierAdmin();
            $d->setEmploye($emp)->setTitre($titre)->setType($type)->setStatut($statut);
            $manager->persist($d);
        }

        $manager->flush();
    }

    private function makeUser(
        ObjectManager $manager, string $prenom, string $nom, string $email,
        array $roles, string $dept, string $poste
    ): User {
        $u = new User();
        $u->setPrenom($prenom)->setNom($nom)->setEmail($email)
          ->setRoles($roles)->setDepartement($dept)->setPoste($poste)
          ->setStatut('Actif');
        $u->setPassword($this->hasher->hashPassword($u, 'password'));
        return $u;
    }
}
