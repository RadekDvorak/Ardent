<?php

namespace Collections;

class SplayTree implements BinarySearchTree {

    use EmptyGuard;
    use IteratorCollection;

    /**
     * @var SplayNode
     */
    private $root;
    /**
     * @var callable
     */
    private $comparator;

    private $header;

    private $size = 0;


    function __construct(callable $comparator = null) {
        $this->comparator = $comparator
            ?: '\Collections\compare';
        $this->header = new SplayNode(null);
    }


    /**
     * @param callable $f
     * @return mixed
     * @throws StateException when the tree is not empty
     */
    function setCompare(callable $f) {
        if ($this->root !== null) {
            throw new StateException;
        }
        $this->comparator = $f;
    }


    function toBinaryTree() {
        return $this->copyNode($this->root);
    }


    /**
     * Insert into the tree.
     * @param mixed $value the item to insert.
     * @return void
     */
    function add($value) {
        if ($this->root == null) {
            $this->root = $this->createNode($value);
            return;
        }
        $this->addImpl($value);
    }


    /**
     * Remove from the tree.
     * @param mixed $value the item to remove.
     */
    function remove($value) {
        if ($this->root === null) {
            return;
        }
        $this->removeImpl($value);
    }


    /**
     * Find the smallest item in the tree.
     */
    function first() {
        $this->emptyGuard(__METHOD__);
        return $this->farthest('left', $this->root);
    }


    /**
     * Find the largest item in the tree.
     */
    function last() {
        $this->emptyGuard(__METHOD__);
        return $this->farthest('right', $this->root);
    }


    /**
     * Find an item in the tree.
     * @param mixed $value
     * @throws LookupException
     * @return mixed
     */
    function get($value) {
        if ($this->root == null) {
            throw new LookupException;
        }
        $this->splay($value);
        if (call_user_func($this->comparator, $this->root->value, $value) !== 0) {
            throw new LookupException;
        }
        return $this->root->value;
    }


    /**
     * @param $item
     * @return bool
     */
    function contains($item) {
        if ($this->root == null) {
            return false;
        }
        $this->splay($item);
        return call_user_func($this->comparator, $this->root->value, $item) === 0;
    }


    /**
     * Test if the tree is logically empty.
     * @return bool true if empty, false otherwise.
     */
    function isEmpty() {
        return $this->root == null;
    }


    function count() {
        return $this->size;
    }


    function clear() {
        $this->root = null;
        $this->header = new SplayNode(null);
        $this->size = 0;
    }


    /**
     * @return BinaryTreeIterator
     */
    function getIterator() {
        $root = $this->copyNode($this->root);
        return new InOrderIterator($root, 0);
    }


    private function addImpl($value) {
        $this->splay($value);
        if (($c = call_user_func($this->comparator, $value, $this->root->value)) === 0) {
            $this->root->value = $value;
            return;
        }
        $n = $this->createNode($value);
        if ($c < 0) {
            $n->left = $this->root->left;
            $n->right = $this->root;
            $this->root->left = null;
        } else {
            $n->right = $this->root->right;
            $n->left = $this->root;
            $this->root->right = null;
        }
        $this->root = $n;
    }


    private function removeImpl($value) {
        $this->splay($value);
        if (call_user_func($this->comparator, $value, $this->root->value) !== 0) {
            return;
        }
        // Now delete the $this->root
        $this->size--;
        if ($this->root->left == null) {
            $this->root = $this->root->right;
        } else {
            $x = $this->root->right;
            $this->root = $this->root->left;
            $this->splay($value);
            $this->root->right = $x;
        }
    }


    private function createNode($value) {
        $this->size++;
        return new SplayNode($value);
    }


    /**
     * @param $direction
     * @param SplayNode $context
     * @return SplayNode
     */
    private function farthest($direction, SplayNode $context) {
        for ($n = $context; $n->$direction != null; $n = $n->$direction) {
            ;
        }
        $this->splay($n->value);
        return $n->value;
    }


    private function copyNode(SplayNode $n = null) {
        if ($n === null) {
            return null;
        }

        $stack = new \SplStack();
        $rootTree = new BinaryTree($n->value);
        $stack->push([$n->right, $rootTree, true]);
        $stack->push([$n->left, $rootTree, false]);

        while (!$stack->isEmpty()) {
            list($node, $parent, $isRight) = $stack->pop();

            if ($node === null) {
                $new = null;
            }
            else {
                $new = new BinaryTree($node->value);

                $stack->push([$node->right, $new, true]);
                $stack->push([$node->left, $new, false]);
            }

            if ($isRight) {
                $parent->setRight($new);
            }
            else {
                $parent->setLeft($new);
            }
        }

        return $rootTree;
    }


    private function rotateRight(SplayNode $t) {
        $y = $t->left;
        $t->left = $y->right;
        $y->right = $t;
        return $y;
    }


    private function rotateLeft(SplayNode $t) {
        $y = $t->right;
        $t->right = $y->left;
        $y->left = $t;
        return $y;
    }


    private function splay_rotate($property, $rotate, $value, $t, $d) {
        if ($t->$property && call_user_func($this->comparator, $value, $t->$property->value) > 0) {
            $t = $this->$rotate($t);
        }
        if ($t->$property == null) {
            return [false, $t, $d];
        }
        $d->$property = $t;
        $d = $t;
        $t = $t->$property;
        return [true, $t, $d];
    }


    private function splay($value) {
        $l = $r = $this->header;
        $t = $this->root;
        $this->header->left = $this->header->right = null;

        do {
            list($continue, $t, $r, $l) = $this->splay_loop($value, $t, $r, $l);
        } while ($continue);

        $this->assemble($t, $r, $l);
    }


    private function assemble($t, $r, $l) {
        $l->right = $t->left;
        $r->left = $t->right;
        $t->left = $this->header->right;
        $t->right = $this->header->left;
        $this->root = $t;
    }


    private function splay_loop($value, $t, $r, $l) {
        $continue = false;
        $result = call_user_func($this->comparator, $value, $t->value);
        if ($result < 0) {
            list($continue, $t, $r) = $this->splay_rotate('left', 'rotateRight', $value, $t, $r);
        } else {
            if ($result > 0) {
                list($continue, $t, $l) = $this->splay_rotate('right', 'rotateLeft', $value, $t, $l);
            }
        }
        return [$continue, $t, $r, $l];
    }


}
