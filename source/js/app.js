
// @codekit-prepend 'lib/jquery.1.12.4.js'
// @codekit-prepend 'lib/jquery.ui.1.12.1.js'

$("form").submit(function (event) {
  event.preventDefault();

  $.ajax({
    url: "pdf.php",
    method: "POST",
    data: $("#form").serialize(),
    dataType: "json",

    beforeSend: function (){
      $("body").animate({
          scrollTop: 0 
      }, "fast");

      $(".loader").fadeIn();
      $("body").css("overflow", "hidden");
    },

    /* If there are any syntax issues in our PHP script we will log them in the console */

    error: function (jqXHR, textStatus, errorThrown) {
      console.log(jqXHR);
      console.log(textStatus);
      console.log(errorThrown);
    },

    complete: function (data) {

      if (data.responseJSON.nameError != undefined) {

        $(".message").removeClass("success");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.nameError).addClass("error").fadeIn();
        });
        grecaptcha.reset();

      } else if (data.responseJSON.emailError != undefined) {

        $(".message").removeClass("success");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.emailError).addClass("error").fadeIn();
        });
        grecaptcha.reset();

      } else if (data.responseJSON.ageError != undefined) {

        $(".message").removeClass("success");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.ageError).addClass("error").fadeIn();
        });
        grecaptcha.reset();

      } else if (data.responseJSON.addressError != undefined) {

        $(".message").removeClass("success");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.addressError).addClass("error").fadeIn();
        });
        grecaptcha.reset();

      } else if (data.responseJSON.contentError != undefined) {

        $(".message").removeClass("success");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.contentError).addClass("error").fadeIn();
        });
        grecaptcha.reset();

      } else if (data.responseJSON.captchaError != undefined) {

        $(".message").removeClass("success");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.captchaError).addClass("error").fadeIn();
        });
        grecaptcha.reset();

      } else if (data.responseJSON.emailSentError != undefined) {

        $(".message").removeClass("error");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.emailSentError).addClass("success").fadeIn();
        });
        grecaptcha.reset();

      } else if (data.responseJSON.pageError != undefined) {

        $(".message").removeClass("error");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.pageError).addClass("error").fadeIn();
        });
        grecaptcha.reset();

      } else if (data.responseJSON.pageSuccess != undefined) {

        $(".message").removeClass("error");
        $(".message").fadeOut(function () {
          $(".message").html(data.responseJSON.pageSuccess).addClass("success").fadeIn();
        });
        grecaptcha.reset();

        /* Reset all form fields after successful submission */

        $("#name").val("");
        $("#email").val("");
        $("#selectDate").val("");
        $("#address").val("");
        $("#content").val("");
        $("#copy").attr("checked", false);

      }

      $(".loader").fadeOut();
      $("body").css("overflow", "scroll");

    }
  });

});

$(document).ready(function () {

  $(function () {
    $("#selectDate").datepicker();
  });

});