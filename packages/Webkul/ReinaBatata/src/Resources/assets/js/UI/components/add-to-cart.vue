<template>
    <form method="POST" @submit.prevent="addToCart">
        <button
            type="submit"
            :disabled="isButtonEnable == 'false' || isButtonEnable == false"
            :class="`btn btn-add-to-cart btn-primary ${addClassToBtn}`">

            <i class="material-icons-outlined text-down-3" v-if="showCartIcon">shopping_cart</i>

            <span class="fs14 fw6 text-up-4" v-text="btnText"></span>
        </button>
    </form>
</template>

<script>
    export default {
        props: [
            'form',
            'btnText',
            'isEnable',
            'csrfToken',
            'productId',
            'reloadPage',
            'moveToCart',
            'showCartIcon',
            'addClassToBtn',
            'productFlatId',
        ],

        data: function () {
            return {
                'isButtonEnable': this.isEnable,
                'qtyText': this.__('checkout.qty'),
            }
        },

        methods: {
            'addToCart': function () {
                this.isButtonEnable = false;
                let url = `${this.$root.baseUrl}/cart/add`;

                this.$http.post(url, {
                    'quantity': 1,
                    '_token': this.csrfToken,
                    'product_id': this.productId,
                })
                .then(response => {
                    this.isButtonEnable = true;

                    if (response.data.status == 'success') {
                        this.$root.miniCartKey++;


                        if (this.moveToCart == "true") {
                            let existingItems = this.getStorageValue('wishlist_product');

                            let updatedItems = existingItems.filter(item => item != this.productFlatId);

                            this.$root.headerItemsCount++;
                            this.setStorageValue('wishlist_product', updatedItems);
                        }

                        // window.showAlert(`alert-success`, this.__('shop.general.alert.success'), response.data.message);
                        
                        if (response.data.redirectionRoute) {
                            window.location.href = response.data.redirectionRoute;
                        }

                        if (this.reloadPage == "1") {
                            window.location.reload();
                        }

                    } else {
                        if (response.data.redirectionRoute) {
                            window.location.href = response.data.redirectionRoute;
                        }
                    }
                })
                .catch(error => {
                    console.log(this.__('error.something_went_wrong'));
                })
            },
        }
    }
</script>