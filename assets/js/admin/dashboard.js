jQuery(document).ready(function ($) {
  // function to redirect to profile tabs on page settings
  $(".redirect-to-profile-tap").on("click", function (e) {
    e.preventDefault();
    localStorage.setItem("activeDataId", "azo-tracking-profile");
    window.location.href = azotracking_dashboard.settings_page_url;
  });

  // function to redirect to authentication tabs on page settings
  $(".redirect-to-authentication-tap").on("click", function (e) {
    e.preventDefault();
    localStorage.setItem("activeDataId", "azo-tracking-authentication");
    window.location.href = azotracking_dashboard.settings_page_url;
  });
  /**
   * Use this event beacuse apexchart has an issue that when chart disappear because there is no data
   * chart will not appear again if we get new data until you resize the browser
   */
  function resizeWindow() {
    window.dispatchEvent(new Event("resize"));
  }

  // Define a function that returns the empty stats message.
  const no_stats_message_html = `
      <span>!</span>
      <p>No data found during this period!</p>
`;

  // Initial of the stats with the last 30 days' data
  let start = moment().subtract(30, "days");
  let end = moment();

  // function to render skeleton while loading stats
  function showSkeletons(target) {
    target.find(".at-skeleton-loading-container").show();
    target.find(".at-rp-card-mid").html("");
    target.find(".no-data-message").html("");
    target.find(".at-rp-chart").hide();
  }
  // function to hide skeleton after loading stats
  function hideSkeletons(target) {
    target.find(".at-skeleton-loading-container").hide();
    target.find(".at-rp-card-mid").show();
    target.find(".at-rp-chart").show();
  }

  /**
   * Attach desctiption on every card items in general stats section
   */
  function attachTooltipBehavior() {
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
  }

  // extract data from the server response
  function extractData(responseData, type) {
    let extractedData = [];
    if (type === "general_stats") {
      for (let key in responseData.data.boxes) {
        if (responseData.data.boxes.hasOwnProperty(key)) {
          let item = responseData.data.boxes[key];
          let bottom = item.bottom || {
            arrow_type: "azo_green",
            main_text: "0%",
            sub_text: "previous period",
          };

          if (
            typeof bottom.main_text === "string" &&
            bottom.main_text.includes("%")
          ) {
            let percentage = parseFloat(bottom.main_text);
            if (!isNaN(percentage)) {
              bottom.main_text = `${Math.ceil(percentage)}%`;
            }
          }
          extractedData.push({
            title: item.title,
            description: item.description,
            number: item.number,
            bottom: bottom,
          });
        }
      }
    } else if (type === "operating_systems_stats") {
      extractedData = responseData.data.os.stats.map((stat) => ({
        os: stat.os,
        sessions: stat.sessions,
      }));
    } else if (type === "browser_stats") {
      extractedData = responseData.data.browser.stats.map((stat) => ({
        browser: stat.browser,
        sessions: stat.sessions,
      }));
    } else if (type === "referer_stats") {
      extractedData = responseData.data.stats.map((stat) => ({
        referer: stat.referer,
        sessions: stat.sessions,
      }));
    } else if (type === "geographic_stats") {
      extractedData = responseData.data.country.stats.map((stat) => ({
        country: stat.country,
        sessions: stat.sessions,
      }));
    } else if (type === "top_pages_stats") {
      extractedData = responseData.data.stats.map((stat) => ({
        title: stat.pageTitle,
        views: stat.screenPageViews,
        avgTime: stat.userEngagementDuration,
        bounceRate: stat.bounceRate,
      }));
    } else if (type === "what_is_happening_stats") {
      extractedData = responseData.data.stats.map((stat) => ({
        titleLink: stat.title_link,
        userEngagementDuration: stat.userEngagementDuration,
        engagedSessions: stat.engagedSessions,
        engagementRate: stat.engagementRate,
      }));
    }
    return extractedData;
  }

  async function callApi(endpoint, sd, ed) {
    let responseData = await AZO_Tracking.get_fetch_data(
      AZO_Tracking.AJAX_URL,
      {
        action: endpoint,
        start_date: sd,
        end_date: ed,
        date_differ: "",
        _wpnonce: AZO_Tracking.getNonceKey(),
      }
    );
    return responseData;
  }

  // fetch data general stats
  async function updateGeneralStats(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let responseData = await callApi(endpoint, start_date, end_date);

      if (responseData.success && responseData) {
        let result = extractData(responseData, endpoint);
        hideSkeletons(container);
        let html = "";
        result.forEach((item) => {
          html += `
          <div class="at-rp-card-item at-rp-${item.title
            .replace(/\s+/g, "-")
            .toLowerCase()}">
            <div class="at-rp-card-item-top">
              <h4>${item.title}</h4>
              <div class="at-icon-description-container">
              <span class="info-link">
                  <img class="info-icon"
                      data-description="${item.description}"
                      src="${
                        azotracking_dashboard.AZOTRACKING_BASE_URL
                      }assets/images/azo-info.svg" />
              </span>
              <div class="at-description-tooltip"></div>
              </div>
            </div>
            <div class="at-rp-card-item-mid">
              <p class="at-rp-value">${item.number}</p>
              <div class="at-rp-trend">
              <div class="at-rp-trend-top ${
                item.bottom.arrow_type === "azo_green"
                  ? "at-rp-uptrend"
                  : "at-rp-downtrend"
              }">
                <span>${item.bottom.main_text}</span>
                <img src="${
                  azotracking_dashboard.AZOTRACKING_BASE_URL
                }assets/images/azo-${
            item.bottom.arrow_type === "azo_green" ? "uptrend" : "downtrend"
          }.svg" />
              </div>
              <div class="at-rp-trend-bottom">
                vs. ${item.bottom.sub_text}
              </div>
              </div>
            </div>
          </div>
        `;
        });
        container.find(".at-rp-card-mid").html(html);
      } else {
        throw Error("Server responded with an error");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    attachTooltipBehavior();
  }

  // fetch data device chart
  const deviceChartOptions = {
    chart: { type: "donut", height: 300 },
    series: [],
    labels: [],
    responsive: [
      {
        breakpoint: 1100,
        options: {
          legend: {
            position: "bottom",
          },
        },
      },

      {
        breakpoint: 500,
        options: {
          chart: {
            height: 250,
          },
        },
      },
    ],
  };

  let deviceChart;

  deviceChart = new ApexCharts(
    document.querySelector("#at-rp-device"),
    deviceChartOptions
  );
  deviceChart.render();

  async function updateDeviceChart(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);

    try {
      let responseData = await callApi(endpoint, start_date, end_date);
      if (responseData.success && responseData.data.success) {
        hideSkeletons(container);

        let stats = responseData.data.charts.visitor_devices.stats;
        let labels = [];
        let series = [];
        let total = 0;

        for (let key in stats) {
          if (stats.hasOwnProperty(key)) {
            labels.push(stats[key].label);
            let number = parseInt(stats[key].number, 10) || 0;
            series.push(number);
            total += number;
          }
        }

        if (total === 0) {
          $("#at-rp-device").hide();
          $("#at-rp-device-skeleton").hide();
          container.find(".no-data-message").html(no_stats_message_html);
        } else {
          container.find(".no-data-message").html("");
          deviceChart.updateOptions({
            labels: labels,
          });
          deviceChart.updateSeries(series);
        }
      } else {
        throw Error("Server responded with an error");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    resizeWindow();
  }

  // fetch data new vs return chart
  const newVsReturnChartOptions = {
    chart: { type: "donut", height: 300 },
    series: [],
    labels: [],
    responsive: [
      {
        breakpoint: 1100,
        options: {
          legend: {
            position: "bottom",
          },
        },
      },
      {
        breakpoint: 500,
        options: {
          chart: {
            height: 250,
          },
        },
      },
    ],
  };

  const newVsReturnChart = new ApexCharts(
    document.querySelector("#at-rp-new-returning"),
    newVsReturnChartOptions
  );
  newVsReturnChart.render();

  async function updateNewVsReturningChart(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let responseData = await callApi(endpoint, start_date, end_date);
      if (responseData.success && responseData.data.success) {
        hideSkeletons(container);
        let stats = responseData.data.charts.new_vs_returning_visitors.stats;
        let labels = [];
        let series = [];
        let total = 0;

        for (let key in stats) {
          if (stats.hasOwnProperty(key)) {
            labels.push(stats[key].label);
            let number = parseInt(stats[key].number, 10) || 0;
            series.push(number);
            total += number;
          }
        }

        if (total === 0) {
          $("#at-rp-new-returning").hide();
          $("#at-rp-new-returning-skeleton").hide();
          container.find(".no-data-message").html(no_stats_message_html);
        } else {
          container.find(".no-data-message").html("");
          newVsReturnChart.updateOptions({
            labels: labels,
          });
          newVsReturnChart.updateSeries(series);
        }
      } else {
        throw Error("Server responded with an error");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    resizeWindow();
  }

  // fetch data operating system list
  async function updateOperatingSystem(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let responseData = await callApi(endpoint, start_date, end_date);
      if (responseData.success && responseData.data && responseData.data.os) {
        let result = extractData(responseData, endpoint);
        hideSkeletons(container);
        if (result.length === 0) {
          container.find(".no-data-message").html(no_stats_message_html);
        } else {
          let tableHTML = `
          <table>
            <thead>
              <tr>
                <th>Operating system</th>
                <th>Sessions</th>
              </tr>
            </thead>
            <tbody>
        `;

          result.forEach((item) => {
            let iconOS = "";
            if (
              item.os.toLowerCase().includes("mac") ||
              item.os.toLowerCase().includes("ios")
            ) {
              iconOS = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-mac.svg" />`;
            } else if (item.os.toLowerCase().includes("window")) {
              iconOS = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-window.svg" />`;
            } else if (item.os.toLowerCase().includes("android")) {
              iconOS = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-android.svg" />`;
            } else {
              iconOS = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-os-default.svg" />`;
            }
            tableHTML += `
            <tr>
              <td>
                <span class="at-rp-os-brand">
                ${iconOS}${item.os}
                </span>
              </td>
              <td>${item.sessions}</td>
            </tr>
          `;
          });

          tableHTML += `
            </tbody>
          </table>
        `;
          container.find(".at-rp-card-list").html(tableHTML);
        }
      } else {
        throw Error("Server responded with an error or data is missing");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    attachTooltipBehavior();
  }

  // fetch data browser list
  async function updateBrowser(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let responseData = await callApi(endpoint, start_date, end_date);
      if (
        responseData.success &&
        responseData.data &&
        responseData.data.browser
      ) {
        let result = extractData(responseData, endpoint);
        hideSkeletons(container);
        if (result.length === 0) {
          container.find(".no-data-message").html(no_stats_message_html);
        } else {
          let tableHTML = `
            <table>
              <thead>
                <tr>
                <th>Browsers</th>
                <th>Visits</th>
                </tr>
              </thead>
              <tbody>
          `;

          result.forEach((item) => {
            let iconBrowser = "";
            if (item.browser.toLowerCase().includes("chrome")) {
              iconBrowser = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-chrome.svg" />`;
            } else if (item.browser.toLowerCase().includes("safari")) {
              iconBrowser = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-safari.svg" />`;
            } else if (item.browser.toLowerCase().includes("firefox")) {
              iconBrowser = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-firefox.svg" />`;
            } else if (item.browser.toLowerCase().includes("microsoft")) {
              iconBrowser = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-edge.svg" />`;
            } else {
              iconBrowser = `<img src="${azotracking_dashboard.AZOTRACKING_BASE_URL}assets/images/azo-browse-default.svg" />`;
            }

            tableHTML += `
              <tr>
                <td>
                  <span class="at-rp-browser-brand">
                  ${iconBrowser}${item.browser}
                  </span>
                </td>
                <td>${item.sessions}</td>
              </tr>
            `;
          });

          tableHTML += `
              </tbody>
            </table>
          `;
          container.find(".at-rp-card-list").html(tableHTML);
        }
      } else {
        throw Error("Server responded with an error or data is missing");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    attachTooltipBehavior();
  }

  // fetch data referrer list
  async function updateReferer(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let responseData = await callApi(endpoint, start_date, end_date);
      if (responseData.success && responseData.data) {
        let result = extractData(responseData, endpoint);
        hideSkeletons(container);
        if (result.length === 0) {
          container.find(".no-data-message").html(no_stats_message_html);
        } else {
          let tableHTML = `
          <table>
            <thead>
              <tr>
              <th>Referrer</th>
              <th>Sessions</th>
              </tr>
            </thead>
            <tbody>
        `;
          result.forEach((item) => {
            tableHTML += `
            <tr>
            <td>${item.referer}</td>
            <td>${item.sessions}</td>
            </tr>
          `;
          });
          tableHTML += `
            </tbody>
          </table>
        `;
          container.find(".at-rp-card-list").html(tableHTML);
        }
      } else {
        throw Error("Server responded with an error or data is missing");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    attachTooltipBehavior();
  }

  // fetch data geographic list
  async function updateGeographic(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let responseData = await callApi(endpoint, start_date, end_date);
      if (responseData.success && responseData.data) {
        let result = extractData(responseData, endpoint);
        hideSkeletons(container);
        if (result.length === 0) {
          container.find(".no-data-message").html(no_stats_message_html);
        } else {
          let tableHTML = `
          <table>
            <thead>
              <tr>
              <th>Country</th>
              <th>Visitors</th>
              </tr>
            </thead>
            <tbody>
        `;
          result.forEach((item) => {
            tableHTML += `
            <tr>
            <td>${item.country}</td>
            <td>${item.sessions}</td>
            </tr>
          `;
          });
          tableHTML += `
            </tbody>
          </table>
        `;
          container.find(".at-rp-card-list").html(tableHTML);
        }
      } else {
        throw Error("Server responded with an error or data is missing");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    attachTooltipBehavior();
  }

  // fetch data top pages list
  async function updateTopPages(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let responseData = await callApi(endpoint, start_date, end_date);
      if (responseData.success && responseData.data) {
        let result = extractData(responseData, endpoint);
        hideSkeletons(container);
        if (result.length === 0) {
          container.find(".no-data-message").html(no_stats_message_html);
        } else {
          let tableHTML = `
          <table>
            <thead>
              <tr>
              <th class="fixed-width-top-pages">Page Title</th>
              <th>Views</th>
              <th>Avg. Time</th>
              <th>Bounce Rate</th>
              </tr>
            </thead>
            <tbody>
        `;
          result.forEach((item) => {
            tableHTML += `
            <tr>
            <td class="fixed-width-top-pages">${item.title}</td>
            <td>${item.views}</td>
            <td>${item.avgTime}</td>
            <td>${item.bounceRate}</td>
            </tr>
          `;
          });
          tableHTML += `
            </tbody>
          </table>
        `;
          container.find(".at-rp-card-list").html(tableHTML);
          setupPagination(endpoint, result.length);
        }
      } else {
        throw Error("Server responded with an error or data is missing");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    attachTooltipBehavior();
  }

  // paginate table data list if too long
  function setupPagination(endpoint, totalItems) {
    const itemsPerPage = 10;
    const totalPages = Math.ceil(totalItems / itemsPerPage);

    if (totalPages <= 1) {
      return;
    }

    let paginationHTML = '<div class="pagination">';

    for (let i = 1; i <= totalPages; i++) {
      paginationHTML += `<a href="#" class="page-link" data-page="${i}">${i}</a>`;
    }
    paginationHTML += "</div>";
    $(`div[data-endpoint="${endpoint}"] .at-rp-card-list`).append(
      paginationHTML
    );

    $(".page-link").on("click", function (e) {
      e.preventDefault();
      const page = $(this).data("page");

      $(".page-link").removeClass("active");

      $(this).addClass("active");

      showPage(page);
    });
    showPage(1);
    $(".page-link").first().addClass("active");
  }

  function showPage(page) {
    const itemsPerPage = 10;
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;

    $(".at-rp-pages-by-views tbody tr").hide();
    $(".at-rp-pages-by-views tbody tr").slice(start, end).show();
  }

  // fetch data what-is-happening list
  async function updateWhatIsHappening(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let responseData = await callApi(endpoint, start_date, end_date);
      if (responseData.success && responseData.data) {
        let result = extractData(responseData, endpoint);
        hideSkeletons(container);
        if (result.length === 0) {
          container.find(".no-data-message").html(no_stats_message_html);
        } else {
          let tableHTML = `
          <table>
            <thead>
              <tr>
              <th>Title / Link</th>
              <th>Engagement Duration</th>
              <th>Engaged Sessions</th>
              <th>Engagement Rate</th>
              </tr>
            </thead>
            <tbody>
        `;
          result.forEach((item) => {
            tableHTML += `
            <tr>
            <td>${item.titleLink}</td>
            <td>${item.userEngagementDuration}</td>
            <td>${item.engagedSessions}</td>
            <td>${item.engagementRate}</td>
            </tr>
          `;
          });
          tableHTML += `
            </tbody>
          </table>
        `;
          container.find(".at-rp-card-list").html(tableHTML);
          // setupPagination(endpoint, result.length);
        }
      } else {
        throw Error("Server responded with an error or data is missing");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    attachTooltipBehavior();
  }

  // fetch data visitors following date range
  const visitorChartOptions = {
    chart: {
      height: 400,
      type: "area",
      parentHeightOffset: 0,
      toolbar: { show: false },
    },
    markers: {
      size: 4,
      colors: ["#0180ff"],
      strokeWidth: 2,
      hover: { size: 6 },
    },
    stroke: { curve: "straight", width: 2, colors: ["#0180ff"] },
    grid: {
      show: true,
      borderColor: "#f2f8fe",
      xaxis: { lines: { show: true } },
    },
    dataLabels: { enabled: false },
    fill: { opacity: 0.3, type: "solid" },
    series: [{ name: "visits", data: [] }],
    xaxis: { type: "datetime", categories: [], tooltip: { enabled: false } },
    tooltip: {
      shared: true,
      hideEmptySeries: false,
      style: { fontSize: "12px", fontFamily: undefined },
      onDatasetHover: { highlightDataSeries: false },
      x: { format: "yyyy-MM-dd" },
      y: {
        formatter: function (value) {
          if (value === 0 || value === 1) {
            return value + " visitor";
          } else {
            return value + " visitors";
          }
        },
      },
    },
  };

  const visitorChart = new ApexCharts(
    $("#at-rp-daily-visitor")[0],
    visitorChartOptions
  );
  visitorChart.render();

  async function updateDailyVisitorChart(endpoint, start_date, end_date) {
    let container = $(`div[data-endpoint="${endpoint}"]`);
    showSkeletons(container);
    try {
      let response = await callApi(endpoint, start_date, end_date);
      if (response.success && response.data && response.data.success) {
        hideSkeletons(container);
        let data = response.data.data;
        let dataMap = {};
        data.forEach((item) => {
          let formattedDate = moment(item.date, "YYYYMMDD").format(
            "YYYY-MM-DD"
          );
          dataMap[formattedDate] = parseInt(item.activeUsers);
        });

        let categories = [];
        let seriesData = [];
        let currentDate = moment(start_date);
        let endDate = moment(end_date);

        while (currentDate <= endDate) {
          let formattedDate = currentDate.format("YYYY-MM-DD");
          categories.push(formattedDate);
          seriesData.push(dataMap[formattedDate] || 0);
          currentDate = currentDate.add(1, "day");
        }
        visitorChart.updateOptions({
          xaxis: { categories: categories },
          series: [{ name: "visits", data: seriesData }],
        });
      } else {
        throw Error("Server responded with an error or data is missing");
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    }
  }
  let showDashboardStats = azotracking_dashboard.show_dashboard_stats;

  let fetchingData = [];

  // callback function handle fetch-data functions following date range
  async function cb(start, end) {
    $("#reportrange span").html(
      start.format("MMM D, YYYY") + " - " + end.format("MMM D, YYYY")
    );

    let start_date = start.format("YYYY-MM-DD");
    let end_date = end.format("YYYY-MM-DD");

    // Wait for all ongoing API calls to complete before starting new ones
    if (fetchingData.length > 0) {
      await Promise.all(fetchingData);
      fetchingData = [];
    }

    $(".body-dashboard-row").each(async function () {
      let endpoint = $(this).data("endpoint");

      if (!showDashboardStats.includes(endpoint)) {
        $(this).hide();
        return;
      }
      let apiCall;
      switch (endpoint) {
        case "general_stats":
          apiCall = updateGeneralStats(endpoint, start_date, end_date);
          break;
        case "daily_visitors_stats":
          apiCall = updateDailyVisitorChart(endpoint, start_date, end_date);
          break;
        case "visitor_devices_stats":
          apiCall = updateDeviceChart(endpoint, start_date, end_date);
          break;
        case "new_vs_returning_visitors_stats":
          apiCall = updateNewVsReturningChart(endpoint, start_date, end_date);
          break;
        case "operating_systems_stats":
          apiCall = updateOperatingSystem(endpoint, start_date, end_date);
          break;
        case "browser_stats":
          apiCall = updateBrowser(endpoint, start_date, end_date);
          break;
        case "referer_stats":
          apiCall = updateReferer(endpoint, start_date, end_date);
          break;
        case "geographic_stats":
          apiCall = updateGeographic(endpoint, start_date, end_date);
          break;
        case "top_pages_stats":
          apiCall = updateTopPages(endpoint, start_date, end_date);
          break;
        case "what_is_happening_stats":
          apiCall = updateWhatIsHappening(endpoint, start_date, end_date);
          break;
        default:
          console.log("Unsupported endpoint:", endpoint);
          apiCall = Promise.resolve();
      }
      fetchingData.push(apiCall);
    });
    await Promise.all(fetchingData);
    fetchingData = [];
  }

  // Initial date range for date picker
  $("#reportrange").daterangepicker(
    {
      startDate: start,
      endDate: end,
      maxDate: moment(),
      ranges: {
        Today: [moment(), moment()],
        Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "Last 7 Days": [moment().subtract(6, "days"), moment()],
        "Last 30 Days": [moment().subtract(29, "days"), moment()],
        "This Month": [moment().startOf("month"), moment().endOf("month")],
        "Last Month": [
          moment().subtract(1, "month").startOf("month"),
          moment().subtract(1, "month").endOf("month"),
        ],
      },
    },
    cb
  );
  cb(start, end);
});
