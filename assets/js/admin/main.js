jQuery(document).ready(function ($) {
  //handle hover and onclick user avatar header
  const $userContainer = $(".azo-tracking-user-container");

  $userContainer.on("click", function (event) {
    event.preventDefault();
    $(this).toggleClass("active");
  });

  $(document).on("click", function (event) {
    if (
      !$userContainer.is(event.target) &&
      $userContainer.has(event.target).length === 0
    ) {
      $userContainer.removeClass("active");
    }
  });

  $userContainer.hover(
    function () {
      $(this).addClass("active");
    },
    function () {
      if (!$userContainer.hasClass("clicked")) {
        $(this).removeClass("active");
      }
    }
  );
  //switch-tab-settings

  if ($("#azo-tracking-settings-page").length) {
    $("#azo-tracking-settings-page div[id^='azo-tracking-']").hide();
    function showDiv(dataId) {
      $("#azo-tracking-settings-page div[id^='azo-tracking-']").hide();

      $("#" + dataId).show();

      $("#azo-tracking-settings-page .azo-tracking-menu li").removeClass(
        "active"
      );

      $(
        "#azo-tracking-settings-page .azo-tracking-menu li a[data-id='" +
          dataId +
          "']"
      )
        .closest("li")
        .addClass("active");
    }

    $("#azo-tracking-settings-page .azo-tracking-menu li a").on(
      "click",
      function (e) {
        e.preventDefault();
        let dataId = $(this).data("id");
        localStorage.setItem("activeDataId", dataId);
        showDiv(dataId);
      }
    );
    let activeDataId = localStorage.getItem("activeDataId");
    if (!activeDataId) {
      activeDataId = "azo-tracking-configuration";
    }
    showDiv(activeDataId);
  }

  //Custom settings info descriptions
  $(".at-icon-description-container .info-icon").hover(
    function () {
      let description = $(this).data("description");
      let tooltip = $(this)
        .closest(".at-icon-description-container")
        .find(".at-description-tooltip");

      tooltip.text(description);
      tooltip.show();
    },
    function () {
      $(this)
        .closest(".at-icon-description-container")
        .find(".at-description-tooltip")
        .hide();
    }
  );

  //Logout google account
  $("#azo-auth-logout").click(async function (e) {
    AZO_Tracking.btn_loading($(this), "Please wait...");
    let responseData = await AZO_Tracking.get_fetch_data(
      AZO_Tracking.AJAX_URL,
      {
        _wpnonce: AZO_Tracking.getNonceKeyByName(
          "azo-tracking-tab-authentication"
        ),
        action: "google_logout",
      }
    );
    if (responseData.success) {
      window.location.reload();
    } else {
      console.log(responseData);
    }
  });

  //custom selects
  $("select").each(function () {
    let $select = $(this);
    let isMultiSelect = $select.hasClass("multi-select");
    let defaultValues = $select.val();

    // Wrap the select in a custom div
    let $customSelectWrapper = $("<div></div>").addClass(
      "custom-select-wrapper"
    );
    $select.wrap($customSelectWrapper);

    let $customSelectDisplay = $("<div></div>").addClass(
      "custom-select-display"
    );
    let placeholderText = $select.find("option:first").text();
    $customSelectDisplay.text(placeholderText);
    $select.before($customSelectDisplay);

    // Populate options container
    let $optionsContainer = $("<div></div>").addClass("custom-options");

    // Handle optgroup and options
    $select.children().each(function (index) {
      if ($(this).is("optgroup")) {
        let $optgroupLabel = $("<div></div>")
          .addClass("optgroup-label")
          .text($(this).attr("label"));
        $optionsContainer.append($optgroupLabel);

        $(this)
          .children()
          .each(function () {
            let $option = $(this);
            let $optionDiv = $("<div></div>")
              .text($option.text())
              .data("value", $option.val())
              .css("display", $option.css("display"));
            $optionsContainer.append($optionDiv);
          });
      } else if ($(this).is("option")) {
        let $option = $(this);
        let $optionDiv;
        if (index === 0) {
          // Hide the first option in the dropdown
          $optionDiv = $("<div></div>")
            .text($option.text())
            .data("value", $option.val())
            .hide();
        } else {
          $optionDiv = $("<div></div>")
            .text($option.text())
            .data("value", $option.val())
            .css("display", $option.css("display"));
        }
        $optionsContainer.append($optionDiv);
      }
    });

    $customSelectDisplay.after($optionsContainer);

    $customSelectDisplay.on("click", function () {
      // Close all other custom select options
      $(".custom-select-display").not(this).removeClass("active");
      $(".custom-options").not($optionsContainer).hide();

      // Toggle the current custom select options
      $(this).toggleClass("active");
      $optionsContainer.toggle();
    });

    $optionsContainer.on("click", "div", function () {
      if (!$(this).hasClass("optgroup-label")) {
        let value = $(this).data("value");
        if (isMultiSelect) {
          let selectedValues = $select.val() || [];
          if (selectedValues.includes(value)) {
            selectedValues = selectedValues.filter(function (val) {
              return val != value;
            });
          } else {
            selectedValues.push(value);
          }
          $select.val(selectedValues).change();
          // Close dropdown after selecting an option
          $optionsContainer.hide();
          $customSelectDisplay.removeClass("active");
        } else {
          $select.val(value).change();
          $customSelectDisplay.text($(this).text());
          $optionsContainer.hide();
          $customSelectDisplay.removeClass("active");
        }
      }
    });

    $(document).on("click", function (e) {
      if (!$(e.target).closest(".custom-select-wrapper").length) {
        $optionsContainer.hide();
        $customSelectDisplay.removeClass("active");
      }
    });

    if (isMultiSelect) {
      $select.on("change", function () {
        let selectedValues = $(this).val() || [];
        let selectedText = selectedValues
          .map(function (value) {
            let $option = $select.find('option[value="' + value + '"]');
            return (
              '<span class="selected-item">' +
              $option.text() +
              '<span class="remove-item" data-value="' +
              value +
              '"> ×</span></span>'
            );
          })
          .join("");
        $customSelectDisplay.html(selectedText || placeholderText);

        // Highlight selected options
        $optionsContainer.children("div").each(function () {
          if (selectedValues.includes($(this).data("value"))) {
            $(this).addClass("selected");
          } else {
            $(this).removeClass("selected");
          }
        });

        if (selectedValues.length > 0) {
          $customSelectDisplay.append('<span class="clear-all">×</span>');
        }
      });

      $customSelectDisplay.on("click", ".remove-item", function (e) {
        e.stopPropagation();
        let value = $(this).data("value");
        let selectedValues = $select.val() || [];
        selectedValues = selectedValues.filter(function (val) {
          return val != value;
        });
        $select.val(selectedValues).change();
      });

      $customSelectDisplay.on("click", ".clear-all", function (e) {
        e.stopPropagation();
        $select.val([]).change();
      });

      // Trigger change to handle default selected values
      $select.val(defaultValues).change();
    } else {
      // Set default placeholder text if single select
      $customSelectDisplay.text($select.find("option:selected").text());
    }

    // Prevent outer window scrolling when reaching top or bottom of custom-options
    $optionsContainer.on("wheel", function (e) {
      var delta = e.originalEvent.deltaY;
      if (this.scrollTop + delta <= 0) {
        this.scrollTop = 0;
        e.preventDefault();
      } else if (
        this.scrollTop + delta >=
        this.scrollHeight - this.clientHeight
      ) {
        this.scrollTop = this.scrollHeight - this.clientHeight;
        e.preventDefault();
      }
    });
  });
});
