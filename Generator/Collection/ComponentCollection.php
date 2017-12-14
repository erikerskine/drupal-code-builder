<?php

namespace DrupalCodeBuilder\Generator\Collection;

use DrupalCodeBuilder\Generator\BaseGenerator;

/**
 * The collection of components for a generate request.
 *
 * This holds several structures:
 * - The linear list of components, which this class can iterate over.
 * - The tree of components arranged by containment.
 */
class ComponentCollection implements \IteratorAggregate {

  /**
   * The list of instantiated components.
   *
   * These are iterated over by this class.
   *
   * @var \DrupalCodeBuilder\Generator\BaseGenerator[]
   */
  private $components = [];

  /**
   * The containment tree.
   *
   * A tree of parentage data for components, as an array keyed by the parent
   * component name, where each value is an array of the names of the child
   * components. So for example, the list of children of component 'foo' is
   * given by $tree['foo'].
   *
   * @var array
   */
  private $tree = [];

  /**
   * Returns the iterator for this object.
   */
  public function getIterator() {
    return new \ArrayIterator($this->components);
  }

  /**
   * Adds a component to the collection.
   *
   * @param $component
   *   The component to add.
   */
  public function addComponent(BaseGenerator $component) {
    $key = $component->getUniqueID();

    if (isset($this->items[$key])) {
      throw new \Exception("Key $key already in use.");
    }

    $this->components[$key] = $component;
  }

  /**
   * Returns whether the collection has a component with the given ID.
   *
   * @param string $id
   *   The component unique ID.
   *
   * @return bool
   *   Whether the collection has a component with this ID.
   */
  public function hasComponent($id) {
    return isset($this->components[$id]);
  }

  /**
   * Gets all components.
   *
   * @return array
   *   The array of components.
   */
  public function getComponents() {
    return $this->components;
  }

  /**
   * Returns the component with the given ID.
   *
   * @param string $id
   *   The component unique ID.
   *
   * @return
   *   The component.
   */
  public function getComponent($id) {
    return $this->components[$id];
  }

  /**
   * Assemble a tree of components, grouped by what they contain.
   *
   * For example, a code file contains its functions; a form component
   * contains the handler functions.
   *
   * This iterates over the flat list of components assembled by
   * ComponentCollector, and re-assembles it as a tree.
   *
   * The tree is an array of parentage data, where keys are the names of
   * components that are parents, and values are flat arrays of component names.
   * The top level of the tree is the root component, whose name is its type,
   * e.g. 'module'.
   * To traverse the tree:
   *  - access the base component name
   *  - iterate over its children
   *  - recursively do the same thing to each child component.
   *
   * Not all components in the component list need to place themselves into the
   * tree, but this means that they will not participate in file assembly.
   *
   * @return
   *  A tree of parentage data for components, as an array keyed by the parent
   *  component name, where each value is an array of the names of the child
   *  components. So for example, the list of children of component 'foo' is
   *  given by $tree['foo'].
   */
  public function assembleContainmentTree() {
    // TODO: lock the collection once this is called.

    $this->tree = [];
    foreach ($this->components as $id => $component) {
      $parent_name = $component->containingComponent();
      if (!empty($parent_name)) {
        assert(isset($this->components[$parent_name]), "Containing component '$parent_name' given by '$id' is not a component ID.");

        $this->tree[$parent_name][] = $id;
      }
    }

    return $this->tree;
  }

  /**
   * Gets the containment tree.
   *
   * @return array
   *   The tree assembled by assembleContainmentTree().
   */
  public function getContainmentTree() {
    // TODO: throw if no tree yet.

    return $this->tree;
  }

}
