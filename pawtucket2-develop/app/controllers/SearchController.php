<?php
/* ----------------------------------------------------------------------
 * app/controllers/SearchController.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2014-2016 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
 	require_once(__CA_APP_DIR__."/helpers/searchHelpers.php");
 	require_once(__CA_MODELS_DIR__.'/ca_metadata_elements.php');
 	require_once(__CA_APP_DIR__."/controllers/FindController.php");
 	
 	class SearchController extends FindController {
 		# -------------------------------------------------------
 		/**
 		 *
 		 */
 		protected $ops_find_type = "search";

 		/**
 		 *
 		 */
 		protected $opo_result_context = null;
 		
 		/**
 		 *
 		 */
 		protected $opa_access_values = array();
 		
 		/**
 		 *
 		 */
 		protected $ops_view_prefix = 'Search';
 		
 		# -------------------------------------------------------
 		/**
 		 *
 		 */
 		public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
 			parent::__construct($po_request, $po_response, $pa_view_paths);
 			if ($this->request->config->get('pawtucket_requires_login')&&!($this->request->isLoggedIn())) {
                $this->response->setRedirect(caNavUrl($this->request, "", "LoginReg", "LoginForm"));
            }
            
            $this->opo_config = caGetBrowseConfig();
            
 			$this->opa_access_values = caGetUserAccessValues($po_request);
 		 	$this->view->setVar("access_values", $this->opa_access_values);
 			$this->view->setVar("find_type", $this->ops_find_type);
 			caSetPageCSSClasses(array("search", "results"));
 		}
 		# -------------------------------------------------------
 		/**
 		 *
 		 */ 
 		public function __call($ps_function, $pa_args) {
			$o_search_config = caGetSearchConfig();
			$pa_options = array_shift($pa_args);
 			
 			$this->view->setVar("config", $this->opo_config);
 			$ps_function = strtolower($ps_function);
 			$ps_type = $this->request->getActionExtra();
 			$vb_is_advanced = ((bool)$this->request->getParameter('_advanced', pInteger) || (strpos(ResultContext::getLastFind($this->request, $vs_class), 'advanced') !== false));
 			$vs_find_type = $vb_is_advanced ? $this->ops_find_type.'_advanced' : $this->ops_find_type;
 			
 			$this->view->setVar('is_advanced', $vb_is_advanced);
 			$this->view->setVar("browse_type", $ps_function);
 			
 			if ($vb_is_advanced) {
 				if (!($va_browse_info = caGetInfoForAdvancedSearchType($ps_function))) {
					// invalid browse type – throw error
					throw new ApplicationException("Invalid advanced search type $ps_function");
				}
			} elseif (!($va_browse_info = caGetInfoForBrowseType($ps_function))) {
 				// invalid browse type – throw error
 				throw new ApplicationException("Invalid browse type $ps_function");
 			}
 			$vs_class = $this->ops_tablename = $va_browse_info['table'];
 			$va_types = caGetOption('restrictToTypes', $va_browse_info, array(), array('castTo' => 'array'));
 			
 			
 			$this->opo_result_context = new ResultContext($this->request, $va_browse_info['table'], $vs_find_type, $ps_function);
 			
 			$ps_view = $this->request->getParameter('view', pString);
 			if ($ps_view == 'jsonData') {
 				$this->view->setVar('context', $this->opo_result_context);
 				$this->render("Browse/browse_results_data_json.php");
 				return;
 			}
 			
 			$this->opo_result_context->setAsLastFind(true);
 			
 			MetaTagManager::setWindowTitle($this->request->config->get("app_display_name").": "._t("Search %1", $va_browse_info["displayName"]).": ".$this->opo_result_context->getSearchExpression());
 			
 			//
 			// Handle advanced search form submissions
 			//
 			$vs_search_expression_for_display = '';
 			if($vb_is_advanced) { 
 				$this->opo_result_context->setSearchExpression(
 					caGetQueryStringForHTMLFormInput($this->opo_result_context, array('matchOnStem' => $o_search_config->get('matchOnStem')))
 				); 
 				if ($vs_search_expression_for_display = caGetDisplayStringForHTMLFormInput($this->opo_result_context)) {
 					$this->opo_result_context->setSearchExpressionForDisplay($vs_search_expression_for_display);
 				}
 			}
 			
 			$this->view->setVar('browseInfo', $va_browse_info);
 			$this->view->setVar('paging', in_array(strtolower($va_browse_info['paging']), array('continuous', 'nextprevious', 'letter')) ? strtolower($va_browse_info['paging']) : 'continuous');
			
 			$this->view->setVar('options', caGetOption('options', $va_browse_info, array(), array('castTo' => 'array')));
 			
 			$va_views = caGetOption('views', $va_browse_info, array(), array('castTo' => 'array'));
 			if(!is_array($va_views) || (sizeof($va_views) == 0)){
				$va_views = array('list' => array(), 'images' => array(), 'timeline' => array(), 'map' => array(), 'timelineData' => array(), 'pdf' => array(), 'xlsx' => array(), 'pptx' => array());
			} else {
				$va_views['pdf'] = $va_views['timelineData'] = $va_views['xlsx'] = $va_views['pptx'] = array();
			}
			if(!in_array($ps_view, array_keys($va_views))) {
				$ps_view = array_shift(array_keys($va_views));
			}
			$va_view_info = $va_views[$ps_view];

 			$vs_format = ($ps_view == 'timelineData') ? 'json' : 'html';
 			
 			caAddPageCSSClasses(array($vs_class, $ps_function));
 			
 			$this->view->setVar('isNav', (bool)$this->request->getParameter('isNav', pInteger));	// flag for browses that originate from nav bar
 			
			$t_instance = $this->getAppDatamodel()->getInstanceByTableName($vs_class, true);
			$vn_type_id = $t_instance->getTypeIDForCode($ps_type);
			
			$this->view->setVar('t_instance', $t_instance);
 			$this->view->setVar('table', $va_browse_info['table']);
 			$this->view->setVar('primaryKey', $t_instance->primaryKey());
		
			$this->view->setVar('browse', $o_browse = caGetBrowseInstance($vs_class));
			$this->view->setVar('views', caGetOption('views', $va_browse_info, array(), array('castTo' => 'array')));
			$this->view->setVar('view', $ps_view);
			$this->view->setVar('viewIcons', $this->opo_config->getAssoc("views"));
		
			//
			// Load existing browse if key is specified
			//
			if ($ps_cache_key = $this->request->getParameter('key', pString)) {
				$o_browse->reload($ps_cache_key);
			}
		
			if (is_array($va_types) && sizeof($va_types)) { $o_browse->setTypeRestrictions($va_types, array('dontExpandHierarchically' => caGetOption('dontExpandTypesHierarchically', $va_browse_info, false))); }
		
			//
			// Clear criteria if required
			//
			
			if ($vs_remove_criterion = $this->request->getParameter('removeCriterion', pString)) {
				$o_browse->removeCriteria($vs_remove_criterion, array($this->request->getParameter('removeID', pString)));
				if($vs_remove_criterion == "_search"){
					$this->opo_result_context->setSearchExpression("*");
				}
			}
			
			if ((bool)$this->request->getParameter('clear', pInteger)) {
				// Clear all refine critera but *not* underlying _search criterion
				$va_criteria = $o_browse->getCriteria();
				foreach($va_criteria as $vs_criterion => $va_criterion_info) {
					if ($vs_criterion == '_search') { continue; }
					$o_browse->removeCriteria($vs_criterion, array_keys($va_criterion_info));
				}
			}
			
				
			if ($this->request->getParameter('getFacet', pInteger)) {
				$vs_facet = $this->request->getParameter('facet', pString);
				$this->view->setVar('facet_name', $vs_facet);
				$this->view->setVar('key', $o_browse->getBrowseID());
				$va_facet_info = $o_browse->getInfoForFacet($vs_facet);
				$this->view->setVar('facet_info', $va_facet_info);
				# --- pull in different views based on format for facet - alphabetical, list, hierarchy
				 switch($va_facet_info["group_mode"]){
					case "alphabetical":
					case "list":
					default:
						$this->view->setVar('facet_content', $o_browse->getFacetContent($vs_facet, array("checkAccess" => $this->opa_access_values)));
						$this->render("Browse/list_facet_html.php");
					break;
					case "hierarchical":
						$this->render("Browse/hierarchy_facet_html.php");
					break;
				}
				return;
			}
		
			//
			// Add criteria and execute
			//
			$vs_search_expression = $this->opo_result_context->getSearchExpression();
			$vs_search_expression_for_display = $this->opo_result_context->getSearchExpressionForDisplay($vs_search_expression); 
			
			if (($o_browse->numCriteria() == 0) && $vs_search_expression) {
				$o_browse->addCriteria("_search", array($vs_search_expression.(($o_search_config->get('matchOnStem') && !preg_match('!\*$!', $vs_search_expression) && preg_match('![\w]+$!', $vs_search_expression)) ? '*' : '')), array($vs_search_expression_for_display));
			}
			if ($vs_search_refine = $this->request->getParameter('search_refine', pString)) {
				$o_browse->addCriteria('_search', array($vs_search_refine.(($o_search_config->get('matchOnStem') && !preg_match('!\*$!', $vs_search_refine) && preg_match('![\w]+$!', $vs_search_refine)) ? '*' : '')), array($vs_search_refine));
			} elseif ($vs_facet = $this->request->getParameter('facet', pString)) {
				$o_browse->addCriteria($vs_facet, array($this->request->getParameter('id', pString)));
			}
			
			//
			// Sorting
			//
			$vb_sort_changed = false;
			$o_block_result_context = null;
 			if (!($ps_sort = $this->request->getParameter("sort", pString))) {
 				// inherit sort setting from multisearch? (used when linking to full results from multisearch result)
 				if ($this->request->getParameter("source", pString) === 'multisearch') {
 					$o_block_result_context = new ResultContext($this->request, $va_browse_info['table'], 'multisearch', $ps_function);
 					if (($ps_sort !== $o_block_result_context->getCurrentSort()) && $o_block_result_context->getCurrentSort()) {
 						$ps_sort = $o_block_result_context->getCurrentSort();
 						$vb_sort_changed = true;
 					}
 				}
 				
 				if (!$ps_sort && !($ps_sort = $this->opo_result_context->getCurrentSort())) {
 					if(is_array(($va_sorts = caGetOption('sortBy', $va_browse_info, null)))) {
 						$ps_sort = array_shift(array_keys($va_sorts));
 						$vb_sort_changed = true;
 					}
 				}
 			}else{
 				$vb_sort_changed = true;
 			}
 			if($vb_sort_changed){
				# --- set the default sortDirection if available
				$va_sort_direction = caGetOption('sortDirection', $va_browse_info, null);
				if($ps_sort_direction = $va_sort_direction[$ps_sort]){
					$this->opo_result_context->setCurrentSortDirection($ps_sort_direction);
				} 			
 			}
 			if (!($ps_sort_direction = $this->request->getParameter("direction", pString))) {
 				if (!($ps_sort_direction = $this->opo_result_context->getCurrentSortDirection())) {
 					$ps_sort_direction = 'asc';
 				}
 			}
 			
 			$this->opo_result_context->setCurrentSort($ps_sort);
 			$this->opo_result_context->setCurrentSortDirection($ps_sort_direction);
 			
			$va_sort_by = caGetOption('sortBy', $va_browse_info, null);
			$this->view->setVar('sortBy', is_array($va_sort_by) ? $va_sort_by : null);
			$this->view->setVar('sortBySelect', $vs_sort_by_select = (is_array($va_sort_by) ? caHTMLSelect("sort", $va_sort_by, array('id' => "sort"), array("value" => $ps_sort)) : ''));
			$this->view->setVar('sortControl', $vs_sort_by_select ? _t('Sort with %1', $vs_sort_by_select) : '');
			$this->view->setVar('sort', $ps_sort);
			$this->view->setVar('sort_direction', $ps_sort_direction);
			
			$va_options = array('checkAccess' => $this->opa_access_values);
			if ($va_restrict_to_fields = caGetOption('restrictSearchToFields', $va_browse_info, null)) {
				$va_options['restrictSearchToFields'] = $va_restrict_to_fields;
			}
			if ($va_exclude_fields_from_search = caGetOption('excludeFieldsFromSearch', $va_browse_info, null)) {
				$va_options['excludeFieldsFromSearch'] = $va_exclude_fields_from_search;
			}
			
			
			if (caGetOption('dontShowChildren', $va_browse_info, false)) {
				$o_browse->addResultFilter('ca_objects.parent_id', 'is', 'null');	
			}
			
			
			$o_browse->execute(array_merge($va_options, array('expandToIncludeParents' => caGetOption('expandToIncludeParents', $va_browse_info, false), 'strictPhraseSearching' => !$vb_is_advanced)));
		
			//
			// Facets
			//
			if ($vs_facet_group = caGetOption('facetGroup', $va_browse_info, null)) {
				$o_browse->setFacetGroup($vs_facet_group);
			}
			$va_available_facet_list = caGetOption('availableFacets', $va_browse_info, null);
			$va_facets = $o_browse->getInfoForAvailableFacets();
			if(is_array($va_available_facet_list) && sizeof($va_available_facet_list)) {
				foreach($va_facets as $vs_facet_name => $va_facet_info) {
					if (!in_array($vs_facet_name, $va_available_facet_list)) {
						unset($va_facets[$vs_facet_name]);
					}
				}
			} 
		
			foreach($va_facets as $vs_facet_name => $va_facet_info) {
				$va_facets[$vs_facet_name]['content'] = $o_browse->getFacetContent($vs_facet_name, array("checkAccess" => $this->opa_access_values));
			}
		
			$this->view->setVar('facets', $va_facets);
		
			$this->view->setVar('key', $vs_key = $o_browse->getBrowseID());
			$this->request->session->setVar($ps_function.'_last_browse_id', $vs_key);
			
		
			//
			// Current criteria
			//
			$va_criteria = $o_browse->getCriteriaWithLabels();
			if (isset($va_criteria['_search']) && (isset($va_criteria['_search']['*']))) {
				unset($va_criteria['_search']['*']);
			}
			$va_criteria_for_display = array();
			foreach($va_criteria as $vs_facet_name => $va_criterion) {
				$va_facet_info = $o_browse->getInfoForFacet($vs_facet_name);
				foreach($va_criterion as $vn_criterion_id => $vs_criterion) {
					$va_criteria_for_display[] = array('facet' => $va_facet_info['label_singular'], 'facet_name' => $vs_facet_name, 'value' => $vs_criterion, 'id' => $vn_criterion_id);
				}
			}
			$this->view->setVar('criteria', $va_criteria_for_display);
		
			// 
			// Results
			//
			$qr_res = $o_browse->getResults(array('sort' => $va_sort_by[$ps_sort], 'sort_direction' => $ps_sort_direction));
			$this->view->setVar('result', $qr_res);
		
			if (!($pn_hits_per_block = $this->request->getParameter("n", pString))) {
 				if (!($pn_hits_per_block = $this->opo_result_context->getItemsPerPage())) {
 					$pn_hits_per_block = $this->opo_config->get("defaultHitsPerBlock");
 				}
 			}
 			$this->opo_result_context->getItemsPerPage($pn_hits_per_block);
			
			$this->view->setVar('hits_per_block', $pn_hits_per_block);
			
			$this->view->setVar('start', $vn_start = $this->request->getParameter('s', pInteger));
			
			$this->opo_result_context->setParameter('key', $vs_key);
			
			if (($vn_key_start = $vn_start - 500) < 0) { $vn_key_start = 0; }
			$qr_res->seek($vn_key_start);
			$this->opo_result_context->setResultList($qr_res->getPrimaryKeyValues(10000));
			if ($o_block_result_context) { $o_block_result_context->setResultList($qr_res->getPrimaryKeyValues(10000)); $o_block_result_context->saveContext();}
			$qr_res->seek($vn_start);
			
			$this->opo_result_context->saveContext();
 			
 			if ($vn_type_id) {
 				if ($this->render("Browse/{$vs_class}_{$vs_type}_{$ps_view}_{$vs_format}.php")) { return; }
 			} 
 			
 			// map
			if ($ps_view === 'map') {
				$va_opts = array('renderLabelAsLink' => false, 'request' => $this->request, 'color' => '#cc0000');
		
				$va_opts['ajaxContentUrl'] = caNavUrl($this->request, '*', '*', 'AjaxGetMapItem', array('browse' => $ps_function,'view' => $ps_view));
	
				$o_map = new GeographicMap(caGetOption("width", $va_view_info, "100%"), caGetOption("height", $va_view_info, "600px"));
				$qr_res->seek(0);
				$o_map->mapFrom($qr_res, $va_view_info['data'], $va_opts);
				$this->view->setVar('map', $o_map->render('HTML', array()));
			}
 			
 			
 			switch($ps_view) {
 				case 'xlsx':
 				case 'pptx':
 				case 'pdf':
 					$this->_genExport($qr_res, $this->request->getParameter("export_format", pString), $vs_search_expression, $this->getCriteriaForDisplay($o_browse));
 					break;
 				case 'timelineData':
 					$this->view->setVar('view', 'timeline');
 					$this->render("Browse/browse_results_timelineData_json.php");
 					break;
 				default:
 					$this->opo_result_context->setCurrentView($ps_view);
 					$this->render("Browse/browse_results_html.php");
 					break;
 			}
 		}
 		# -------------------------------------------------------
 		# Advanced search
 		# -------------------------------------------------------
		/** 
		 * 
		 */
		public function advanced() {
 			$ps_function = strtolower($this->request->getActionExtra());
 			
 			if (!($va_search_info = caGetInfoForAdvancedSearchType($ps_function))) {
 				// invalid advanced search type – throw error
 				throw new ApplicationException("Invalid advanced search type");
 			}
 			$vs_class = $va_search_info['table'];
 			$va_types = caGetOption('restrictToTypes', $va_search_info, array(), array('castTo' => 'array'));
 			
 			$this->opo_result_context = new ResultContext($this->request, $va_search_info['table'], $this->ops_find_type.'_advanced', $ps_function);
 			$this->opo_result_context->setAsLastFind();
 			
 			MetaTagManager::setWindowTitle($this->request->config->get("app_display_name").": "._t("Search %1", $va_search_info["displayName"]));
 			$this->view->setVar('searchInfo', $va_search_info);
 			$this->view->setVar('options', caGetOption('options', $va_search_info, array(), array('castTo' => 'array')));
 			
 			$va_default_form_values = $this->opo_result_context->getParameter("pawtucketAdvancedSearchFormContent_{$ps_function}");
 			$va_default_form_booleans = $this->opo_result_context->getParameter("pawtucketAdvancedSearchFormBooleans_{$ps_function}");
 			
 			$this->opo_result_context->saveContext();
 			
 			caSetAdvancedSearchFormInView($this->view, $ps_function, $va_search_info['view'], array('request' => $this->request));
 			
 			$this->render($va_search_info['view']);
		}
 		# -------------------------------------------------------
		/** 
		 * Generate the URL for the "back to results" link from a browse result item
		 * as an array of path components.
		 */
 		public static function getReturnToResultsUrl($po_request) {
 			$va_ret = array(
 				'module_path' => '',
 				'controller' => 'Search',
 				'action' => $po_request->getAction(),
 				'params' => array(
 					'key'
 				)
 			);
			return $va_ret;
 		}
 		# -------------------------------------------------------
 		/**
 		 * Returns summary of current browse parameters suitable for display.
 		 *
 		 * @return string Summary of current browse criteria ready for display
 		 */
 		public function getCriteriaForDisplay($po_browse=null) {
 			$va_criteria = $po_browse->getCriteriaWithLabels();
 			if (!sizeof($va_criteria)) { return ''; }
 			$va_criteria_info = $po_browse->getInfoForFacets();
 			
 			$va_buf = array();
 			foreach($va_criteria as $vs_facet => $va_vals) {
 				foreach($va_vals as $vn_i => $vs_val) {
 					$va_vals[$vn_i] = preg_replace("![A-Za-z_\./]+[:]{1}!", "", $vs_val);
 					$va_vals[$vn_i] = trim(preg_replace("![\*].!", "", $va_vals[$vn_i]));
 					$va_vals[$vn_i] = trim(preg_replace("![\(\)]+!", " ", $va_vals[$vn_i]));
 				}
 				$va_buf[] = caUcFirstUTF8Safe($va_criteria_info[$vs_facet]['label_singular']).': '.join(", ", $va_vals);
 			}
 			
 			return join(" / ", $va_buf);
  		}
 		# -------------------------------------------------------
	}