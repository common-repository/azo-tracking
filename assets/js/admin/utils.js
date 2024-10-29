let AZO_Tracking = {};
jQuery(document).ready(function ($) {
  AZO_Tracking = {
    AJAX_URL: AZO.AJAX_URL,
    //define function comicApp
    getNonceKey: function () {
      var nonce_key = $("#_wpnonce").val();
      if (nonce_key.length > 0) return nonce_key;
      else return "";
    },
    getNonceKeyByName(name) {
      console.log(name);
      var nonce_key = $("#" + name).val();
      if (nonce_key.length > 0) return nonce_key;
      else return "";
    },
    call_api: async function (request) {
      try {
        const response = await fetch(request);
        if (!response.ok) {
          throw new Error("Server responded with an error");
        }
        const responseData = await response.json();
        return responseData;
      } catch (error) {
        throw error;
      }
    },
    get_fetch_data: async function (url, data = null) {
      let params = data ? new URLSearchParams(data) : "";
      const request = new Request(url + "?" + params.toString(), {
        method: "GET",
        headers: new Headers({
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        }),
      });
      return await this.call_api(request);
    },
    post_fetch_data: async function (url, data) {
      //Check if data is an object and convert it to a string
      const formattedData = { ...data };
      if (data.data && typeof data.data === "object") {
        formattedData.data = JSON.stringify(data.data);
      }
      const request = new Request(url, {
        method: "POST",
        body: new URLSearchParams(formattedData),
        headers: new Headers({
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        }),
      });
      return await this.call_api(request);
    },
    btn_loading: function (btn, text = "Loading...") {
      btn.prop("disabled", true);
      btn.html(text);
    },
  };
});
