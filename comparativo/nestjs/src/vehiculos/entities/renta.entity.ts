import { Entity, PrimaryGeneratedColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Vehiculo } from './vehiculo.entity';

@Entity('rentas')
export class Renta {
  @PrimaryGeneratedColumn()
  id: number;

  @ManyToOne(() => Vehiculo, { nullable: false, onDelete: 'RESTRICT' })
  @JoinColumn({ name: 'vehiculo_id' })
  vehiculo: Vehiculo;
}