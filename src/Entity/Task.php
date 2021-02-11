<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false, length=1000)
     * @Assert\NotBlank
     */
    private $text;

    /**
     * @ORM\Column(type="boolean", options={"default=false"})
     */
    private $performed = false;

    /**
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $createDate;

    public function __construct()
    {
        $this->setCreateDate(new \DateTime('now'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }

    /**
     * @return bool
     */
    public function isPerformed(): bool
    {
        return $this->performed;
    }

    /**
     * @param bool $performed
     */
    public function setPerformed(bool $performed): void
    {
        $this->performed = $performed;
    }

    /**
     * @return mixed
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param mixed $createDate
     */
    public function setCreateDate($createDate): void
    {
        $this->createDate = $createDate;
    }

}
