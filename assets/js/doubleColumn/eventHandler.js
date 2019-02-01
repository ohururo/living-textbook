require('../event/eventTypes');

// Import routing
import Routing from 'fos-routing';

/**
 * This module handles events from the content of the application
 */
(function (eHandler, types) {

  // Initialize current url
  eHandler.currentUrl = $('#data-iframe').attr('src');

  /**
   * Register message handler for communication
   */
  window.addEventListener('message', function (event) {
    console.info('Event received in double column', event.data);

    let type = event.data.type;
    let data = event.data.payload;

    switch (type) {
      case types.CHECK_DOUBLE_COLUMN:
        onCheckDoubleColumn(data);
        break;
      case types.PAGE_LOAD:
        onPageLoad(data);
        break;
      case types.PAGE_LOADED:
        onPageLoaded(data);
        break;
      case types.PAGE_SUBMIT:
        onPageSubmit();
        break;
      case types.TOGGLE_CONCEPT_BROWSER:
        onToggleConceptBrowser();
        break;
      case types.CONCEPT_SELECTED:
        onConceptSelected(data);
        break;
      case types.SHOW_CONCEPT:
        onShowConcept(data);
        break;
      case types.OPEN_LEARNING_PATH_BROWSER:
        onOpenLearningPath(data);
        break;
      case types.CLOSE_LEARNING_PATH_BROWSER:
        onCloseLearningPath(data);
        break;
      case types.NAVIGATE_LEARNING_PATH:
        onNavigateLearningPath(data);
        break;
      default:
        console.warn('Unknown event!', type);
    }
  });

  /**
   * Return the checksum to indicate it's here
   * @param data
   */
  function onCheckDoubleColumn(data) {
    eDispatch.returnDoubleColumnChecksum(data.checksum);
  }

  /**
   * Load the concept page
   * @param data
   */
  function onConceptSelected(data) {
    // Forward to page load
    onPageLoad({url: Routing.generate('app_concept_show', {_studyArea: _studyArea, concept: data.id})})
  }

  /**
   * Update the iframe src url
   * @param data
   */
  function onPageLoad(data) {
    // Check options
    data.options = data.options || {};
    if (data.options.topLevel) {
      window.location.href = data.url;
      return;
    }

    dw.iframeLoad(data.url);
  }

  eHandler.onPageLoad = function (data) {
    onPageLoad(data);
  };

  /**
   * Update the page url
   * @param data
   */
  function onPageLoaded(data) {
    // Calculate new url
    var newUrl = '/page' + data.url;
    eHandler.currentUrl = data.url;
    var state = {
      currentUrl: eHandler.currentUrl,
      currentTitle: document.title
    };

    // Update or replace the state
    if (window.location.pathname === newUrl || window.location.pathname === '/' || window.history.state === null) {
      window.history.replaceState(state, '', newUrl);
    } else {
      window.history.pushState(state, '', newUrl);
    }

    // Set the title
    document.title = data.title;
  }

  /**
   * Show the loaded screen
   */
  function onPageSubmit() {
    dw.iframeLoader(true);
  }

  /**
   * Toggle concept browser state
   */
  function onToggleConceptBrowser() {
    dw.toggleWindow();
  }

  /**
   * A concept must be shown
   * @param data
   */
  function onShowConcept(data) {
    if (data.id === -1) {
      dw.openWindow(function () {
        cb.centerView();
      });
    } else {
      dw.openWindow(function () {
        cb.moveToConceptById(data.id);
      });
    }
  }

  /**
   * Opens the learning path
   * @param data
   */
  function onOpenLearningPath(data) {
    lpb.openBrowser(data.id);
  }

  /**
   * Closes the learning path
   * @param data
   */
  function onCloseLearningPath(data) {
    lpb.closeBrowser();
  }

  /**
   * Load the requested learning path url
   * @param data
   */
  function onNavigateLearningPath(data) {
    // Forward to page load
    onPageLoad({url: Routing.generate('app_learningpath_show', {_studyArea: _studyArea, learningPath: data.id})})
  }

}(window.eHandler = window.eHandler || {}, window.eType));
