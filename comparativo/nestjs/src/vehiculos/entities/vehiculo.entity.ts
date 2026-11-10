import { Entity, Column, PrimaryGeneratedColumn } from 'typeorm';

@Entity('vehiculos')
export class Vehiculo {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({ unique: true })
  placa: string;

  @Column()
  marca: string;

  @Column()
  modelo: string;

  @Column()
  anno: number;

  @Column()
  kilometraje: number;

  @Column()
  categoria_id: number;

  @Column()
  estado_id: number;
}