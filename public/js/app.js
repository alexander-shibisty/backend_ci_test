var app = new Vue({
	el: '#app',
	data: {
		login: '',
		pass: '',
		post: false,
		invalidLogin: false,
		invalidPass: false,
		invalidSum: false,
		posts: [],
		addSum: 0,
		amount: 0,
		likes: 0,
		commentText: '',
		packs: [
			{
				id: 1,
				price: 5
			},
			{
				id: 2,
				price: 20
			},
			{
				id: 3,
				price: 50
			},
		],
	},
	computed: {
		test: function () {
			var data = [];
			return data;
		}
	},
	mounted: function() {
		var errorMessage = this.getUrlParam('error');
		
		if(errorMessage !== null && errorMessage !== '') {
			this.errorNotification(errorMessage);
		}
	},
	created(){
		var self = this
		axios
			.get('/main_page/get_all_posts')
			.then(function (response) {
				self.posts = response.data.posts;
			})
	},
	methods: {
		logout: function () {
			console.log ('logout');
		},
		logIn: function () {
			var self= this;
			if(self.login === ''){
				self.invalidLogin = true
			}
			else if(self.pass === ''){
				self.invalidLogin = false
				self.invalidPass = true
			}
			else{
				self.invalidLogin = false
				self.invalidPass = false
				axios.post('/login', {
					login: self.login,
					password: self.pass
				})
					.then(function (response) {
						var data = response.data;

						if(data && typeof data.status !== 'undefined') {
							if(data.status === 'error') {
								document.location.href = document.location.pathname 
									+ '?error=' 
									+ data.error_message;

							} else {
								document.location.reload();
							}
						}
					})
			}
		},
		fiilIn: function () {
			var self= this;
			if(self.addSum === 0){
				self.invalidSum = true
			}
			else{
				self.invalidSum = false
				axios.post('/main_page/add_money', {
					sum: self.addSum,
				})
					.then(function (response) {
						setTimeout(function () {
							$('#addModal').modal('hide');
						}, 500);
					})
			}
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.post = response.data.post;
					if(self.post){
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})
		},
		addLike: function (id) {
			var self= this;
			axios
				.get('/main_page/like')
				.then(function (response) {
					self.likes = response.data.likes;
				})

		},
		buyPack: function (id) {
			var self= this;
			axios.post('/main_page/buy_boosterpack', {
				id: id,
			})
				.then(function (response) {
					self.amount = response.data.amount
					if(self.amount !== 0){
						setTimeout(function () {
							$('#amountModal').modal('show');
						}, 500);
					}
				})
		},
		addComment: function(event) {
			var self = this;

			event.preventDefault();
			var jForm = $(event.target);
			var jMessage = jForm.find('[name="message"]');
			var jPostId = jForm.find('[name="post_id"]');

			this.commentText = jMessage.val();
			jMessage.val('');
			jMessage.blur();

			axios.post('/comment', {
				'post_id': jPostId.val(),
				'message': this.commentText,
			}).then(function(response) {
				var data = response.data;

				if(data && typeof data.status !== 'undefined') {
					if(data.status === 'error') {
						jMessage.focus();
						jMessage.val(this.commentText);

						this.errorNotification(data.error_message);
					} else {
						jMessage.focus();

						self.post = data.post;
					}
				}
			});
		},

		getUrlParam: function(paramName) {
			var urlParams = new URLSearchParams(document.location.search);

			return urlParams.get(paramName);
		},
		errorNotification: function(message) {
			new Noty({
				type: 'error',
				layout: 'topRight',
				text: message,
			}).show();
		}
	}
});

