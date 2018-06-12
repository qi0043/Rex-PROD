<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
	/**
	* A wrapper around the DOMDocument and DOMXPath classes.  It is provided for convenience.
	*/
	class XMLWrapper
	{
		private $domDoc;
		private $xpathDoc;
                
                public $num_node_notfound;
		
		public function __construct($parmarray)
		{
                        $xmlString = $parmarray['xmlString'];
			$this->domDoc = new DOMDocument();
			$this->domDoc->loadXML($xmlString);
			$this->xpathDoc = new DOMXPath($this->domDoc);
                        $this->num_node_notfound = 0;
		}
		
		
		public function __toString()
		{
			return $this->domDoc->saveXML();
		}
		
		
		public function node($xpath, $nodeContext=null)
		{
			if ($nodeContext == null)
			{
				$nodeList = $this->xpathDoc->query($xpath);
                                #echo $xpath; ####
			}
			else
			{
				$nodeList = $this->xpathDoc->query($xpath, $nodeContext);
			}
			return $this->singleNodeFromList( $nodeList );
		}

	
		public function nodeValue($xpath, $nodeContext=null)
		{
			if ($nodeContext == null)
			{
				$nodeList = $this->xpathDoc->query($xpath);
			}
			else
			{
				$nodeList = $this->xpathDoc->query($xpath, $nodeContext);
			}
                        
                        #if (isset($nodeList)) ####
                           #var_dump($nodeList);
                        #echo $xpath . '<br>'; ####
                        
			return $this->singleNodeValueFromList( $nodeList );
		}
		
		
		public function nodeList($xpath)
		{
			return $this->xpathDoc->query($xpath);
		}
		
		
		public function numNodes($xpath)
		{

			return $this->xpathDoc->query($xpath)->length;
		}
		
		
		public function setNodeValue($xpath, $value, $createIfNotExists = true)
		{
			$node = $this->singleNodeFromList( $this->nodeList($xpath) );
			if ($node == null)
			{
				$node = $this->createNodeFromXPath($xpath);
			}
			$node->nodeValue = $value;
			return $node;
		}
		
		
		public function createNode($parent, $nodeName)
		{
			$node = $this->domDoc->createElement($nodeName);
			$parent->appendChild($node);
			return $node;
		}
		
		
		public function createNodeFromXPath($xpath)
		{
			$node = $this->node($xpath);
			if ($node == null)
			{
				$xpathElements = explode('/', $xpath);
				$path = '';
				$node = $this->domDoc->documentElement;
				foreach ($xpathElements as $element)
				{
					if (!empty($element))
					{
						$path = $path.'/'.$element;
						$nextNode = $this->node($path);
						if ($nextNode == null)
						{
							$node = $this->createNode($node, $element);
						}
						else
						{
							$node = $nextNode;
						}
					}
				}
			}
			return $node;
		}
		
		
		public function createAttribute($parent, $attrName)
		{
			$node = $this->domDoc->createAttribute($attrName);
			$parent->appendChild($node);
			return $node;
		}
		
		
		public function deleteNodeFromXPath($xpath)
		{
			$node = $this->node($xpath);
			if ($node != null)
			{
				$node->parentNode->removeChild($node);
			}
		}
		
		public function nodeExists($xpath)
		{
			$exists = FALSE;
			$node = $this->node($xpath);
			if ($node != null)
			{
				$exists = TRUE;
			}
			return $exists;
		}
		
		private function singleNodeValueFromList($nodeList)
		{
			$node = $this->singleNodeFromList($nodeList);
			if ($node == null)
			{
                                $this->num_node_notfound ++; ####
                                #echo 'not found here!!!!!';####
                                #var_dump($nodeList);
				return '';
			}
			return $node->nodeValue;
		}
		
		
		private function singleNodeFromList($nodeList)
		{
			if ($nodeList->length > 0)
			{
				$node = $nodeList->item(0);
				return $node;
			}
			return null;
		}
                
                #added for creating text node
                public function createTextNode($node, $texts)
		{
                        $domtext = $this->domDoc->createTextNode($texts);
			$node->appendChild($domtext);
			return $node;
		}
	}
?>