<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>
	<!--Start Footer Three-->
<div class="footer">
  <div class="container">
    <div class="row">
      <div class="col-sm-5">
        <div class="logo-section"> <img src="<?php echo get_template_directory_uri(); ?>/images/1-Logo-Horizontal-Light-BG-footer.png" />
          <?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?><?php dynamic_sidebar( 'sidebar-2' ); ?><?php endif; ?>
			<a href="https://www.facebook.com/sitramincbusiness"><img src="https://sitraminc.com/wp-content/uploads/2021/09/facebook-1.png" alt="Facebook" style="width:40px;height:40px; margin:5px"></a>
			<a href="https://www.linkedin.com/in/denis-antony-8056851a4/"><img src="https://sitraminc.com/wp-content/uploads/2021/09/linkedin.png" alt="Linkedin" style="width:40px;height:40px; margin:5px"></a>
			<a href="https://twitter.com/Sitraminc1"><img src="https://sitraminc.com/wp-content/uploads/2021/09/twitter.png" alt="Facebook" style="width:40px;height:40px; margin:5px"></a>
        </div>
      </div>
      <div class="col-sm-7">
      <?php if ( is_active_sidebar( 'sidebar-3' ) ) : ?><?php dynamic_sidebar( 'sidebar-3' ); ?><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!--End Footer Three--> 

<!--Start CopyRight Section-->
<div class="copyright">
        <div>
          <p>Copyrights 2020 Sitram Inc. All Rights Reserved.</p>
        </div>
      </div>
<!--End CopyRight Section--> 
 <!--End CopyRight Section--> 

<script>
	var selectedProducts = [];
</script>
<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>
<script>
$( document ).ready(function() {
	$(document).on("click", ".footable-pagination-wrapper ul.pagination", function(){
		//higlight selected row
		//get ids from selectedProducts, add highlight class
		$(".ninja_footable tr").removeClass("highlight");
		if(selectedProducts){
			 setTimeout(function() {
				   for(var i=0; i <= selectedProducts.length - 1; i++){
					   var id = selectedProducts[i]["id"];
					   $(".ninja_footable tr."+id).addClass("highlight");	
				   }
			   }, 500);
		}
		
		
	});
	
	$(document).on("click", ".ninja_footable tr[class^=ninja_table_row]", function(event){
		var currentRow=$(this);
		var id = currentRow[0].classList.item(1);
		if(!currentRow.hasClass("highlight")){
			currentRow.addClass("highlight");
		}else{
			//remove product from array and update count
			currentRow.removeClass("highlight");
			selectedProducts = $.grep(selectedProducts, function(e){ 
    		 return e.id != id; 
			});	
			$('#selected').html(selectedProducts.length);
			return;
		}
		
		//continue adding to array
		var currentHeaderRow=currentRow.closest('table').find('.footable-header');
		var totalColums = currentRow[0].cells.length;

		var selectedProductObj ={};
		const exists = selectedProducts.some(el => el.id === id);
		if(exists) { return; } //do not add duplicates
		
		selectedProductObj["id"] =  id;
		for(var i=0; i <= totalColums - 1; i++){
			var headerVal = currentHeaderRow.find("th:eq("+ i +")").text();
			var columnVal= currentRow.find("td:eq("+ i +")").text();
			selectedProductObj[headerVal] = columnVal;
		}
	
		selectedProducts.push(selectedProductObj);
		$('#selected').html(selectedProducts.length);
	});
	
	function buildSelectedProducts(){
		$("#accordion_new").html("");
		if(selectedProducts){		
			var selectedProductsHtml = "";
			var stockNumber = "";
			for(var i=0; i <= selectedProducts.length - 1; i++){
				var listInnerHtml = "";
				var productObj = selectedProducts[i];
				for(const property in productObj){
					if(property == "id") { continue;}  //id is not for display -- internal use
					if(property.toLowerCase() == "stock number" || property.toLowerCase() == "part number"){
						stockNumber = productObj[property];
						continue;
					}
					listInnerHtml+= "<li><a href='#'><span class='descr'>" + property + " : </span> <span>"+productObj[property]+"</span></a></li>"
				}
				var uiDisplayNumber = i+1;
				selectedProductsHtml += "<li class='selectedrow'><input type='hidden' name='title[]' value='"+stockNumber+"'><div class='first_drop_down'><div class='left_side_product'><p>"+uiDisplayNumber+"</p></div><div class='center_side_product'><div class='link drpd"+uiDisplayNumber+"' id='"+uiDisplayNumber+"'>"+stockNumber+"<i class='fa fa-chevron-down'></i></div><ul class='submenu_new' id='submenu_new"+uiDisplayNumber+"'>" + 
					listInnerHtml + 
					"</ul></div><div class='right_side_product'><input type='number' name='quantity[]' min='1' value='1'></div><div class='delet_right_side_product'> <i class='fa fa-trash deleterecord' id='deleterecord"+uiDisplayNumber+"' data-id='"+productObj["id"]+"'></i> </div></div></li>"
			}
			
			$("#accordion_new").append(selectedProductsHtml);

		}
	}
	
	$('#myBtn').click(function(){
			buildSelectedProducts();
	});
	
	 $(document).on("click", ".deleterecord", function(event){       
		var id = $(this).data('id');
		
		$(".ninja_footable tr."+id).removeClass("highlight");		
		selectedProducts = $.grep(selectedProducts, function(e){ 
    		 return e.id != id; 
		});	
		
		$('#selected').html(selectedProducts.length);
		$(this).parents('li').first().remove();
    });
	
	  $(".sdFilter").click(function(){
		  if ($(this).hasClass("active")){ //already selected
			  return;
		  }
		  $(this).siblings().removeClass("active");
		  $(this).addClass("active");
		  
		  var searchText = $(this).text();
		   var chk = $(".footable-filtering-search :input[type='checkbox']");
		  //clear search text if 'show all'
		  if(searchText == "Show All") {
			  searchText = "";
			  chk.each(function () { //reset desc filter
				 this.checked = true;
			  });
		  }else{
			 chk.each(function () {
			  var val = this.nextSibling.nodeValue.trim();
			   if(val.toUpperCase() == "FIBER QTY" || val.toUpperCase() == "FIBER TYPE"){ 
					  this.checked = true; //only search in mentioned fields
				  }else{
					  this.checked = false;
				  }
		  	 });
		  }
		  
		  var input = $(".footable-filtering-search :input[type='text']");				 
		  var searchBtn = $(".footable-filtering-search :button[type='submit']");
		  searchBtn.click();  
		  
		  setTimeout(function() { 
			  input.val(searchText);
			  searchBtn.click();
		  }, 500);
  });	
});
	

	
</script> 
<script>
$( document ).ready(function() {
$(document).on("click", ".link", function(event){
var rid = this.id;
$("#submenu_new"+rid).toggle();
    });
});
</script>
<script>
$(function() {
	var Accordion = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;

		// Variables privadas
		var links = this.el.find('.link');
		// Evento
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
	}

	Accordion.prototype.dropdown = function(e) {
		var $el = e.data.el;
			$this = $(this),
			$next = $this.next();

		$next.slideToggle();
		$this.parent().toggleClass('open');

		if (!e.data.multiple) {
			$el.find('.submenu').not($next).slideUp().parent().removeClass('open');
		};
	}	

	var accordion = new Accordion($('#accordion'), false);
});
</script> 
<script type="text/javascript">

function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}
function validate(){
    var error = false;
	var quantity = [];
    //checking if quantity is greater than 0
    $.each( $("input[name='quantity[]']"), function(index,value){
        if( value.value.length == 0 || parseInt(value.value) <= 0){
            $("#warning").html("Please enter a valid value for quantity. Minimum Quantity: 1").css('color','red');   
            error = true;
            return;
        }
		quantity.push(parseInt(value.value));
    });
	 
	 if(error) { return;}
	
	//checking if valid name is entered
	var name = $("#name").val();
	 if( name == '') { 
		$("#warning").html("Please enter a valid name.").css('color','red');
		$("#name").addClass("input-error");
		error =  true;
	 	return;
	 }
	
	 $("#warning").html(""); 
	
	 //checking if valid email is entered.
	 var email = $("#email").val();
	 if( !isEmail(email)) { 
		$("#warning").html("Please enter a valid email address.").css('color','red');
		$("#email").addClass("input-error");
		error =  true;
	 	return;
	 }
	
	 $("#warning").html(""); //reset error message
	
	//checking if address line 1 is entered
	 var address1 = $("#address1").val();
	 if( address1 == '') { 
		$("#warning").html("Please enter a valid street address.").css('color','red');
		$("#address1").addClass("input-error");
		error =  true;
	 	return;
	 }
	
	 $("#warning").html(""); 
	
	//checking if city is entered
	var city = $("#city").val();
	 if( city == '') { 
		$("#warning").html("Please enter a valid city.").css('color','red');
		$("#city").addClass("input-error");
		error =  true;
	 	return;
	 }
	
	 $("#warning").html(""); 
	
	//checking if state is entered
	var state = $("#state").val();
	 if( state == '') { 
		$("#warning").html("Please enter a valid state/province.").css('color','red');
		$("#state").addClass("input-error");
		error =  true;
	 	return;
	 }
	
	 $("#warning").html(""); 
	
	//checking if country is entered
	var country = $("#country").val();
	 if( country == '') { 
		$("#warning").html("Please enter a valid country.").css('color','red');
		$("#country").addClass("input-error");
		error =  true;
	 	return;
	 }
	
	 $("#warning").html(""); 
	
	//checking if zip code is entered
	var zip = $("#zip").val();
	 if( zip == '') { 
		$("#warning").html("Please enter a valid zip.").css('color','red');
		$("#zip").addClass("input-error");
		error =  true;
	 	return;
	 }
	
	 $("#warning").html(""); 
	
	
	
	
   	var title = [];
	$.each( $("input[name='title[]']"), function(index,value){
        title.push(value.value);
    });
		
	jQuery.post("https://sitraminc.com/wp-content/themes/sitraminc/email.php", {
		title: title,
		quantity: quantity,
		name:$("#name").val(),
		email:$("#email").val(),
		companyName:$("#companyName").val(),
		address1:$("#address1").val(),
		address2:$("#address2").val(),
		city:$("#city").val(),
		state:$("#state").val(),
		country:$("#country").val(),
		zip:$("#zip").val(),
	}).done(function(response, status) {
       if( response.trim() == "Failed to send email")
		{
			$("#warning").html("Something went wrong, please try again. If the error persist, plase contact us. Thank you").css('color','red');
			return;
		}
		
		$("#warning").html("Thank you for the order. We will get back at the earliest.").css('color','green');
		 setTimeout(function() {
			 $("#warning").html("");
			 modal.style.display = "none";
		}, 3000);
    })
    .fail(function(jqXHR){
        if(jqXHR.status==500 || jqXHR.status==0){
            // internal server error or internet connection broke  
        }
    });
		
        
}

$(".woocommerce #primary").addClass("container mt-4 pt-4");

</script> 
<!-- <script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
	var btn = document.getElementById("shopByCatButton");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>  -->
<script>
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("myBtn");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks the button, open the modal 
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
<script>
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("myBtn1");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks the button, open the modal 
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
<script>
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("myBtn2");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks the button, open the modal 
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
<script>
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("myBtn3");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks the button, open the modal 
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
<script>
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("myBtn4");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks the button, open the modal 
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
<script>
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("myBtn5");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks the button, open the modal 
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
<script>
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("myBtn6");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks the button, open the modal 
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
<script>
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("myBtn7");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks the button, open the modal 
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
<script>
$(function() {
	var accordion_new = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;

		// Variables privadas
		var links = this.el.find('.link');
		// Evento
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
	}

	accordion_new.prototype.dropdown = function(e) {
		var $el = e.data.el;
			$this = $(this),
			$next = $this.next();

		$next.slideToggle();
		$this.parent().toggleClass('open');

		if (!e.data.multiple) {
			$el.find('.submenu_new').not($next).slideUp().parent().removeClass('open');
		};
	}	

	var accordion_new = new accordion_new($('#accordion_new'), false);
});
</script> 

<?php wp_footer(); ?>

