import 'package:everlms/core/localization/tl.dart';
import 'package:everlms/core/utils/dimensions.dart';
import 'package:everlms/core/widgets/custom_app_bar.dart';
import 'package:everlms/core/widgets/custom_button.dart';
import 'package:everlms/feature/cheek_out/presentation/widgets/payment_method.dart';
import 'package:everlms/feature/cheek_out/presentation/widgets/success_message.dart';
import 'package:flutter/material.dart';

class CheekOutScreen extends StatelessWidget {
  const CheekOutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: tl(context).check_out,
      ),
      body: ListView.builder(
        itemCount: 3,
        itemBuilder: (context, index) {
          //todo: change below
          //return const CartItem(isCoupon: false);
          return Container();
        },
      ),
      bottomNavigationBar: SizedBox(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.end,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // todo: remove comment
            //const CalculatePayment(),
            const PaymentMethod(),
            Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: CustomButton(
                onPressed: () {
                  showDialog(
                    context: context,
                    builder: (_) => const SuccessMessage(),
                  );
                },
                buttonText: tl(context).complete_payment,
              ),
            ),
            const SizedBox(
              height: Dimensions.paddingSizeDefault,
            ),
          ],
        ),
      ),
    );
  }
}
