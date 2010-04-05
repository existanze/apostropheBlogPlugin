  public function executeRemoveFilter(sfWebRequest $request)
  {
    $filter_field = $request->getParameter('filter_field');
    $filter_value = $request->getParameter('filter_value');
    
    $filters = $this->getUser()->getAttribute('<?php echo $this->getModuleName() ?>.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    $filters[$filter_field] = null;
    $this->getUser()->setAttribute('<?php echo $this->getModuleName() ?>.filters', $filters, 'admin_module');
    
    $this->redirect('@<?php echo $this->getUrlForAction('list') ?>');
  }
