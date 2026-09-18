import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:domicilios_ubate/main.dart';

void main() {
  testWidgets('La app arranca en el splash', (tester) async {
    await tester.pumpWidget(const ProviderScope(child: DomiciliosUbateApp()));
    await tester.pump();

    // El splash siempre renderiza el nombre y el tagline (aunque con
    // opacidad creciente). El test verifica que los widgets existen.
    expect(find.text('Domicilios Ubaté'), findsOneWidget);
    expect(find.text('Comida que llega rápido'), findsOneWidget);
  });
}
