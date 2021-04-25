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
	computed: {},
	created(){
		var self = this
		axios
			.get('/main_page/get_all_posts')
			.then((response) => {
				self.posts = response.data.posts;
			})
	},
	methods: {
		logout() {
			console.log ('logout');
		},
		logIn() {
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
				axios.post('/main_page/login', {
					login: self.login,
					password: self.pass
				})
					.then(function (response) {
						setTimeout(function () {
							$('#loginModal').modal('hide');
							if (response.data.status === 'success') {
								window.location.reload();
							}
						}, 500);
					})
			}
		},
		fiilIn() {
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
		openPost (id) {
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
		addLike(id) {
			var self= this;
			axios
				.get('/main_page/like')
				.then(function (response) {
					self.likes = response.data.likes;
				})

		},
		buyPack(id) {
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
		addComment() {
			axios.post(
				'/main_page/comment',
				{
					post_id: this.post.id,
					parent_comment_id: null,
					text: this.commentText
				}
			).then(
				(response) => {
					if (response?.data?.status === 'success') {
						this.post = response.data.post;
						this.commentText = '';
					} else {
						setTimeout(function () {
							$('#errorModal').modal('show');
						}, 500);
					}
				}
			);
		}
	}
});

