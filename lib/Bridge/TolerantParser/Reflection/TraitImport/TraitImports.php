<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport;

use Countable;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\TraitSelectOrAliasClause;
use Microsoft\PhpParser\Node\TraitUseClause;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Visibility;
use RuntimeException;

class TraitImports implements Countable
{
    private $imports = [];

    public function __construct(ClassDeclaration $classDeclaration)
    {
        $traitImports = [];
        foreach ($classDeclaration->classMembers->classMemberDeclarations as $memberDeclaration) {
            if (false === $memberDeclaration instanceof TraitUseClause) {
                continue;
            }

            $traitNames = $memberDeclaration->traitNameList->getElements();
            foreach ($traitNames as $traitName) {
                $traitName = (string) $traitName;

            }


            if (null === $memberDeclaration->traitSelectAndAliasClauses) {
                $this->imports[$traitName] = new TraitImport($traitName);
                continue;
            }

            $aliases = [];

            foreach ($memberDeclaration->traitSelectAndAliasClauses as $selectAndAliasClauses)
            {
                foreach($selectAndAliasClauses as $clause) {
                    if (false === $clause instanceof TraitSelectOrAliasClause) {
                        continue;
                    }

                    $memberName = (string) $clause->name;
                    $targetName = (string) $clause->targetName;

                    $aliases[$memberName] = new TraitAlias($memberName, $this->visiblity($clause), $targetName);
                }
            }

            $this->imports[$traitName] = new TraitImport($traitName, $aliases);
        }
    }

    public function has(string $name) 
    {
        return isset($this->imports[$name]);
    }

    public function get(string $name): TraitImport
    {
        if (!array_key_exists($name, $this->imports)) {
            throw new RuntimeException(sprintf(
                'Trait import "%s" does not exist', $name
            ));
        }

        return $this->imports[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->imports);
    }

    private function visiblity(TraitSelectOrAliasClause $clause)
    {
        foreach ($clause->modifiers as $modifier) {
            if ($modifier->kind === TokenKind::PrivateKeyword) {
                return Visibility::private();
            }

            if ($modifier->kind === TokenKind::ProtectedKeyword) {
                return Visibility::protected();
            }
        }


        return Visibility::public();
    }

}
