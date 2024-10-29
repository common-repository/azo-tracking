jQuery(document).ready(function ($) {
  function updateCustomSelect($select) {
    let $customSelectWrapper = $select.closest(".custom-select-wrapper");
    let $customSelectDisplay = $customSelectWrapper.find(
      ".custom-select-display"
    );
    let $optionsContainer = $customSelectWrapper.find(".custom-options");

    $optionsContainer.empty();

    let $firstOption = $select.find("option:first");
    let firstOptionText = $firstOption.text();
    let selectedText =
      $select.find("option:selected").text() || firstOptionText;
    $customSelectDisplay.text(selectedText);

    $select.children().each(function (index) {
      let $option = $(this);
      let $optionDiv = $("<div></div>")
        .text($option.text())
        .data("value", $option.val())
        .css("display", $option.css("display"));
      $optionsContainer.append($optionDiv);
    });

    let firstVisibleOption = $optionsContainer.children("div:visible").first();
    if (firstVisibleOption.length) {
      $customSelectDisplay.text(firstVisibleOption.text());
    }
  }

  function createDataStreamAjax() {
    $("#submit").addClass("disable");
    $("#azo-tracking-profile\\[dashboard_data_stream\\]").addClass("disable");
    $.ajax({
      url: settings_ajax.ajax_url,
      type: "POST",
      data: {
        action: "create_ga_streams",
        property_id: $("#grouped_profile").val(),
        _wpnonce: $("#azo-tracking-profile #_wpnonce").val(),
      },
      success: function (response) {
        const data = response.data.message;
        if (data && Object.keys(data).length > 0) {
          const key = Object.keys(data)[0];
          const newOption = $("<option/>", {
            "data-property": data[key].property_id,
            class: "data-stream-option",
            selected: true,
            value: data[key].measurement_id,
            text: data[key].stream_name,
          });
          $("#azo-tracking-profile\\[dashboard_data_stream\\]").append(
            newOption
          );

          $("#azo-tracking-profile\\[dashboard_data_stream\\]").removeClass(
            "disable"
          );
          $("#submit").removeClass("disable");
          updateCustomSelect(
            $("#azo-tracking-profile\\[dashboard_data_stream\\]")
          );
        }
      },
      error: function (error) {
        console.log("Error:", error);
      },
    });
  }
  const propertyValue = $("#grouped_profile").val();
  $(`.data-stream-option[data-property='${propertyValue}']`).show();

  $("#grouped_profile").on("change", function () {
    let propertyValueChange = this.value;

    $(".data-stream-option").hide();
    $(`.data-stream-option[data-property='${propertyValueChange}']`).show();
    if (
      $(`.data-stream-option[data-property='${propertyValueChange}']`).length ==
      0
    ) {
      createDataStreamAjax();
      return;
    }

    let visibleOptions = $(
      `.data-stream-option[data-property='${propertyValueChange}']`
    );
    updateCustomSelect($("#azo-tracking-profile\\[dashboard_data_stream\\]"));

    let firstVisibleOption = visibleOptions.first();
    if (firstVisibleOption.length) {
      $("#azo-tracking-profile\\[dashboard_data_stream\\]")
        .val(firstVisibleOption.val())
        .change();
      $("#azo-tracking-profile\\[dashboard_data_stream\\]")
        .closest(".custom-select-wrapper")
        .find(".custom-select-display")
        .text(firstVisibleOption.text());
    }
    firstVisibleOption.prop("selected", true);
  });

  updateCustomSelect($("#azo-tracking-profile\\[dashboard_data_stream\\]"));

  $("#azo-tracking-configuration\\[linker_cross_domain_tracking\\]").click(
    function () {
      if (
        $("#azo-tracking-configuration\\[linker_cross_domain_tracking\\]").is(
          ":checked"
        )
      ) {
        $(".linker-tracking-list").slideDown();
      } else {
        $(".linker-tracking-list").slideUp();
      }
    }
  );
  if (
    $("#azo-tracking-configuration\\[linker_cross_domain_tracking\\]").is(
      ":checked"
    )
  ) {
    $(".linker-tracking-list").show();
  } else {
    $(".linker-tracking-list").hide();
  }
});
